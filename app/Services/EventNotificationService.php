<?php

namespace App\Services;

use App\Mail\NewEventPostedMail;
use App\Models\BirthdayAttendee;
use App\Models\Event;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EventNotificationService
{
    /**
     * Dispatch automatic Email and (when configured) SMS notifications
     * to attendees who opted in to future Marines of Tahlequah event alerts.
     *
     * @return array{emails_sent: int, sms_queued: int}
     */
    public static function notifySubscribers(Event $event): array
    {
        $emailsSent = self::sendEmailNotifications($event);
        $smsQueued = self::sendSmsNotifications($event);

        return [
            'emails_sent' => $emailsSent,
            'sms_queued' => $smsQueued,
        ];
    }

    /**
     * Send an email notification to all opted-in attendees who selected 'email' or 'both'.
     */
    public static function sendEmailNotifications(Event $event): int
    {
        $emailSubscribers = BirthdayAttendee::query()
            ->where('notify_future_events', true)
            ->whereIn('preferred_contact_method', ['email', 'both'])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get()
            ->unique(fn (BirthdayAttendee $a) => strtolower(trim((string) $a->email)));

        $sentCount = 0;

        foreach ($emailSubscribers as $subscriber) {
            try {
                Mail::to(trim($subscriber->email))
                    ->send(new NewEventPostedMail($event, $subscriber->full_name));
                $sentCount++;
            } catch (\Throwable $e) {
                Log::warning('Failed to send new event email to ' . $subscriber->email . ': ' . $e->getMessage());
            }
        }

        return $sentCount;
    }

    /**
     * Send or queue SMS/text notifications for opted-in attendees who selected 'phone' or 'both'.
     * Ready for Twilio or webhook SMS gateway integration via config('services.sms').
     */
    public static function sendSmsNotifications(Event $event): int
    {
        $smsSubscribers = self::getSmsSubscribers();

        if ($smsSubscribers->isEmpty()) {
            return 0;
        }

        $message = sprintf(
            "Marines of Tahlequah: New Event! %s on %s. Details: %s",
            $event->event_name,
            $event->event_datetime?->format('M j, Y g:i A') ?? '',
            route('events.show', $event)
        );

        $smsEnabled = (bool) config('services.sms.enabled', false);
        if (!$smsEnabled) {
            Log::info('SMS notification prepared (SMS platform not yet enabled in .env)', [
                'event_id' => $event->id,
                'recipient_count' => $smsSubscribers->count(),
                'message_preview' => $message,
            ]);

            return $smsSubscribers->count();
        }

        $sentCount = 0;
        $driver = config('services.sms.driver', 'twilio');

        foreach ($smsSubscribers as $subscriber) {
            $phone = self::normalizePhone((string) $subscriber->phone);
            if (!$phone) {
                continue;
            }

            try {
                if ($driver === 'twilio' && config('services.sms.sid') && config('services.sms.token')) {
                    $sid = config('services.sms.sid');
                    $token = config('services.sms.token');
                    $from = config('services.sms.from');

                    Http::withBasicAuth($sid, $token)
                        ->asForm()
                        ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                            'From' => $from,
                            'To' => $phone,
                            'Body' => $message,
                        ]);

                    $sentCount++;
                } elseif (config('services.sms.webhook_url')) {
                    Http::post(config('services.sms.webhook_url'), [
                        'to' => $phone,
                        'name' => $subscriber->full_name,
                        'message' => $message,
                        'event_id' => $event->id,
                    ]);

                    $sentCount++;
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to send SMS to ' . $phone . ': ' . $e->getMessage());
            }
        }

        return $sentCount;
    }

    /**
     * Get unique SMS-opted-in attendees with valid phone numbers.
     *
     * @return Collection<int, BirthdayAttendee>
     */
    public static function getSmsSubscribers(): Collection
    {
        return BirthdayAttendee::query()
            ->where('notify_future_events', true)
            ->whereIn('preferred_contact_method', ['phone', 'both'])
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get()
            ->unique(fn (BirthdayAttendee $a) => preg_replace('/\D+/', '', (string) $a->phone));
    }

    /**
     * Normalize US phone numbers to E.164 format (+1XXXXXXXXXX).
     */
    public static function normalizePhone(string $rawPhone): ?string
    {
        $digits = preg_replace('/\D+/', '', $rawPhone);
        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return '+' . $digits;
        }

        return strlen($digits) >= 10 ? '+' . $digits : null;
    }
}
