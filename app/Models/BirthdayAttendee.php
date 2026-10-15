<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BirthdayAttendee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'zip',
        'notify_future_events',
        'preferred_contact_method',
        'attendee_type',
        'service_status',
        'branch',
        'rank',
        'attending_in_honor',
        'honoring_marine_name',
        'honoring_relationship',
        'attended_before',
        'years_attended',
        'is_donor',
        'donation_details',
        'participates_in_raffles',
        'has_won_raffle',
        'raffle_prize_won',
        'suggestions',
        'event_year',
        'user_id',
    ];

    protected $casts = [
        'notify_future_events' => 'boolean',
        'attending_in_honor' => 'boolean',
        'attended_before' => 'boolean',
        'years_attended' => 'integer',
        'is_donor' => 'boolean',
        'participates_in_raffles' => 'boolean',
        'has_won_raffle' => 'boolean',
        'event_year' => 'integer',
    ];

    /**
     * Get the user account associated with this check-in, if any.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Full formatted name including optional middle name.
     */
    public function getFullNameAttribute(): string
    {
        return trim(collect([$this->first_name, $this->middle_name, $this->last_name])->filter()->implode(' '));
    }

    /**
     * Full formatted address string.
     */
    public function getFullAddressAttribute(): string
    {
        $cityStateZip = trim(collect([
            $this->city,
            trim(($this->state ?? '').' '.($this->zip ?? '')),
        ])->filter()->implode(', '));

        return trim(collect([$this->address, $cityStateZip])->filter()->implode(', '));
    }

    /**
     * Human-readable label for attendee category.
     */
    public function getAttendeeTypeLabelAttribute(): string
    {
        return match ($this->attendee_type) {
            'marine' => 'U.S. Marine',
            'other_branch' => $this->branch ? "{$this->branch} Service Member / Veteran" : 'Other Military Branch',
            'family_honor' => 'Family Member (In Honor of a Marine)',
            'supporter' => 'Community Supporter / Donor',
            default => ucfirst((string) $this->attendee_type),
        };
    }

    /**
     * Human-readable label for service status.
     */
    public function getServiceStatusLabelAttribute(): string
    {
        return match ($this->service_status) {
            'active' => 'Active Duty',
            'veteran' => 'Veteran',
            'reserve_guard' => 'Reserve / National Guard',
            'civilian' => 'Civilian / Family / Supporter',
            default => 'N/A',
        };
    }

    /**
     * Human-readable label for future event notification preference.
     */
    public function getContactPreferenceLabelAttribute(): string
    {
        if (!$this->notify_future_events) {
            return 'Opted Out';
        }

        return match ($this->preferred_contact_method) {
            'email' => 'Email',
            'phone' => 'Phone / Text (SMS)',
            'both' => 'Email & Phone / Text',
            default => 'Yes',
        };
    }

    /**
     * Whether this attendee wants email notifications for future events.
     */
    public function wantsEmailNotifications(): bool
    {
        return $this->notify_future_events
            && in_array($this->preferred_contact_method, ['email', 'both'], true)
            && !empty($this->email);
    }

    /**
     * Whether this attendee wants phone/SMS notifications for future events.
     */
    public function wantsSmsNotifications(): bool
    {
        return $this->notify_future_events
            && in_array($this->preferred_contact_method, ['phone', 'both'], true)
            && !empty($this->phone);
    }
}
