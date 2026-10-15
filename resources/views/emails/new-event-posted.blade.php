<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->event_name }} - Marines of Tahlequah</title>
</head>
<body style="margin: 0; padding: 0; background-color: #18181b; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #f4f4f5;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #18181b; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #27272a; border: 1px solid #3f3f46; border-radius: 16px; overflow: hidden;">
                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(90deg, #991b1b 0%, #b45309 100%); padding: 24px 32px; text-align: center;">
                            <div style="font-size: 12px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; color: #fde68a; margin-bottom: 6px;">
                                Semper Fidelis &bull; Marines of Tahlequah
                            </div>
                            <h1 style="margin: 0; font-size: 24px; font-weight: 900; color: #ffffff;">
                                New Upcoming Event Posted
                            </h1>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 32px;">
                            <p style="margin: 0 0 16px; font-size: 16px; color: #e4e4e7;">
                                Hello <strong>{{ $recipientName }}</strong>,
                            </p>

                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.6; color: #d4d4d8;">
                                A new event has just been posted by the <strong>Marines of Tahlequah</strong>. Because you asked to be notified of future events, here are the details:
                            </p>

                            {{-- Event Box --}}
                            <div style="background-color: #18181b; border: 1px solid #3f3f46; border-left: 4px solid #dc2626; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                                <h2 style="margin: 0 0 8px; font-size: 20px; font-weight: 800; color: #fbbf24;">
                                    {{ $event->event_name }}
                                </h2>
                                <p style="margin: 0 0 14px; font-size: 14px; font-weight: 600; color: #f87171;">
                                    Date &amp; Time: {{ $event->event_datetime?->format('l, F j, Y \a\t g:i A') }}
                                </p>
                                <div style="font-size: 14px; line-height: 1.6; color: #e4e4e7; white-space: pre-line;">{{ $event->description }}</div>
                            </div>

                            {{-- CTA Button --}}
                            <div style="text-align: center; margin: 28px 0;">
                                <a href="{{ route('events.show', $event) }}" style="display: inline-block; background-color: #b91c1c; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 12px 28px; border-radius: 10px;">
                                    View Event Details on Website
                                </a>
                            </div>

                            <p style="margin: 24px 0 0; font-size: 13px; color: #a1a1aa; text-align: center;">
                                Thank you for supporting our local service members, veterans, and families in Tahlequah and Cherokee County.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background-color: #18181b; padding: 16px 32px; text-align: center; border-top: 1px solid #3f3f46; font-size: 12px; color: #71717a;">
                            &copy; {{ date('Y') }} Marines of Tahlequah. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
