<!doctype html>
<html lang="{{ app()->getLocale() === 'en' ? 'en' : 'vi' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ __('Payment::ticket-email.subjects.'.$event) }}</title>
</head>
<body style="margin:0;padding:24px 12px;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#17233b;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:680px;margin:0 auto;background:#fff;border:1px solid #e1e6eb;border-radius:14px;overflow:hidden;">
        <tr>
            <td style="line-height:0;font-size:0;">
                <img src="cid:futa-ticket-banner@futabus.vn" width="680" alt="FUTA Bus Lines" style="display:block;width:100%;max-width:680px;height:auto;">
            </td>
        </tr>
        @if(!in_array($event, ['booking_confirmed', 'exchange_completed'], true))
            <tr>
                <td style="padding:24px 28px 12px;">
                    <h1 style="margin:0 0 12px;font-size:22px;line-height:1.35;color:#e94e1b;">{{ __('Payment::ticket-email.subjects.'.$event) }}</h1>
                    <p style="margin:0 0 8px;font-size:15px;line-height:1.6;">{{ __('Payment::ticket-email.greeting', ['name' => $customerName ?: __('Payment::ticket-email.passenger')]) }}</p>
                    <p style="margin:0;font-size:14px;line-height:1.65;color:#53647b;">{{ __('Payment::ticket-email.intro.'.$event) }}</p>
                </td>
            </tr>
        @endif

        @if(in_array($event, ['booking_confirmed', 'exchange_completed'], true))
            @foreach($tickets as $index => $ticket)
                <tr>
                    <td style="padding:16px 28px 8px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #e1e6eb;border-radius:12px;">
                            <tr>
                                <td colspan="2" align="center" style="padding:18px 16px 4px;">
                                    <img src="cid:futa-ticket-qr-{{ $index }}@futabus.vn" width="220" height="220" alt="{{ __('Payment::ticket-email.qr_alt', ['code' => $ticket['code']]) }}" style="display:block;width:220px;height:220px;border:1px solid #edf0f2;border-radius:8px;">
                                </td>
                            </tr>
                            <tr><td colspan="2" align="center" style="padding:6px 16px 18px;font-size:12px;color:#66758a;">{{ __('Payment::ticket-email.qr_hint') }}</td></tr>
                            <tr><td colspan="2" style="padding:14px 16px;background:#fff4ef;color:#e94e1b;font-size:13px;font-weight:bold;">{{ __('Payment::ticket-email.ticket_title') }}</td></tr>
                            @if($event === 'exchange_completed')<tr><td style="padding:8px 16px;color:#66758a;font-size:13px;">{{ __('Payment::ticket-email.old_ticket') }}</td><td align="right" style="padding:8px 16px;font-size:14px;">{{ $details['old_ticket_code'] ?? '—' }}</td></tr>@endif
                            <tr><td style="padding:8px 16px;color:#66758a;font-size:13px;">{{ __('Payment::ticket-email.ticket_code') }}</td><td align="right" style="padding:8px 16px;font-size:14px;font-weight:bold;">{{ $ticket['code'] }}</td></tr>
                            <tr><td style="padding:8px 16px;color:#66758a;font-size:13px;">{{ __('Payment::ticket-email.booking_code') }}</td><td align="right" style="padding:8px 16px;font-size:14px;">{{ $ticket['booking_code'] }}</td></tr>
                            <tr><td style="padding:8px 16px;color:#66758a;font-size:13px;">{{ __('Payment::ticket-email.passenger') }}</td><td align="right" style="padding:8px 16px;font-size:14px;">{{ $ticket['passenger_name'] }}</td></tr>
                            @if($ticket['passenger_phone'])
                                <tr><td style="padding:8px 16px;color:#66758a;font-size:13px;">{{ __('Payment::ticket-email.phone') }}</td><td align="right" style="padding:8px 16px;font-size:14px;">{{ $ticket['passenger_phone'] }}</td></tr>
                            @endif
                            <tr><td style="padding:8px 16px;color:#66758a;font-size:13px;">{{ __('Payment::ticket-email.route') }}</td><td align="right" style="padding:8px 16px;font-size:14px;font-weight:600;">{{ $ticket['origin'] }} → {{ $ticket['destination'] }}</td></tr>
                            <tr><td style="padding:8px 16px;color:#66758a;font-size:13px;">{{ __('Payment::ticket-email.departure') }}</td><td align="right" style="padding:8px 16px;font-size:14px;">{{ \Illuminate\Support\Carbon::parse($ticket['departure_at'])->format('H:i, d/m/Y') }}</td></tr>
                            @if($ticket['arrival_at'])
                                <tr>
                                    <td style="padding:8px 16px;color:#66758a;font-size:13px;">{{ __('Payment::ticket-email.arrival') }}</td>
                                    <td align="right" style="padding:8px 16px;font-size:14px;">
                                        {{ \Illuminate\Support\Carbon::parse($ticket['arrival_at'])->format('H:i, d/m/Y') }}
                                    </td>
                                </tr>
                            @endif
                            <tr><td style="padding:8px 16px;color:#66758a;font-size:13px;">{{ __('Payment::ticket-email.vehicle') }}</td><td align="right" style="padding:8px 16px;font-size:14px;">{{ $ticket['bus_type'] ?: '—' }}</td></tr>
                            <tr><td style="padding:8px 16px;color:#66758a;font-size:13px;">{{ __('Payment::ticket-email.seat') }}</td><td align="right" style="padding:8px 16px;font-size:14px;font-weight:600;">{{ $ticket['seat'] ?: '—' }}</td></tr>
                            <tr>
                                <td style="padding:8px 16px;color:#66758a;font-size:13px;">{{ __('Payment::ticket-email.pickup') }}</td>
                                <td align="right" style="padding:8px 16px;font-size:14px;">
                                    {{ $ticket['pickup_name'] }}
                                    @if($ticket['pickup_address'])
                                        <br><span style="font-size:12px;color:#66758a;">{{ $ticket['pickup_address'] }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 16px;color:#66758a;font-size:13px;">{{ __('Payment::ticket-email.dropoff') }}</td>
                                <td align="right" style="padding:8px 16px;font-size:14px;">
                                    {{ $ticket['dropoff_name'] }}
                                    @if($ticket['dropoff_address'])
                                        <br><span style="font-size:12px;color:#66758a;">{{ $ticket['dropoff_address'] }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr><td style="padding:8px 16px 14px;color:#66758a;font-size:13px;">{{ __('Payment::ticket-email.fare') }}</td><td align="right" style="padding:8px 16px 14px;color:#e94e1b;font-size:15px;font-weight:bold;">{{ number_format($ticket['price'], 0, ',', '.') }} đ</td></tr>
                        </table>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td style="padding:16px 28px 8px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #e1e6eb;border-radius:12px;">
                        <tr><td colspan="2" style="padding:14px 16px;background:#fff4ef;color:#e94e1b;font-size:13px;font-weight:bold;">{{ __('Payment::ticket-email.'.($event === 'cancellation_requested' ? 'cancellation_title' : 'refund_title')) }}</td></tr>
                        <tr><td style="padding:10px 16px;color:#66758a;">{{ __('Payment::ticket-email.ticket_code') }}</td><td align="right" style="padding:10px 16px;font-weight:bold;">{{ $tickets[0]['code'] ?? '—' }}</td></tr>
                        @if(isset($details['reference']))<tr><td style="padding:10px 16px;color:#66758a;">{{ __('Payment::ticket-email.reference') }}</td><td align="right" style="padding:10px 16px;">{{ $details['reference'] }}</td></tr>@endif
                        @if(isset($details['fee']))<tr><td style="padding:10px 16px;color:#66758a;">{{ __('Payment::ticket-email.cancellation_fee') }}</td><td align="right" style="padding:10px 16px;">{{ number_format($details['fee'], 0, ',', '.') }} đ</td></tr>@endif
                        @if(isset($details['refund']))
                            <tr>
                                <td style="padding:10px 16px 14px;color:#66758a;">{{ __('Payment::ticket-email.refund_amount') }}</td>
                                <td align="right" style="padding:10px 16px 14px;color:#e94e1b;font-weight:bold;">
                                    {{ number_format($details['refund'], 0, ',', '.') }} đ
                                </td>
                            </tr>
                        @endif
                        @if(!empty($details['bank_reference']))<tr><td style="padding:10px 16px;color:#66758a;">{{ __('Payment::ticket-email.bank_reference') }}</td><td align="right" style="padding:10px 16px;">{{ $details['bank_reference'] }}</td></tr>@endif
                        @if(!empty($details['bank_paid_at']))
                            <tr>
                                <td style="padding:10px 16px;color:#66758a;">{{ __('Payment::ticket-email.bank_paid_at') }}</td>
                                <td align="right" style="padding:10px 16px;">
                                    {{ \Illuminate\Support\Carbon::parse($details['bank_paid_at'])->format('H:i, d/m/Y') }}
                                </td>
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
        @endif

        <tr>
            <td style="padding:14px 28px 24px;color:#66758a;font-size:12px;line-height:1.6;">
                @if(in_array($event, ['booking_confirmed', 'exchange_completed'], true))
                    <h1 style="margin:0 0 8px;font-size:20px;line-height:1.35;color:#e94e1b;">{{ __('Payment::ticket-email.subjects.'.$event) }}</h1>
                    <p style="margin:0 0 8px;font-size:14px;line-height:1.6;">{{ __('Payment::ticket-email.greeting', ['name' => $customerName ?: __('Payment::ticket-email.passenger')]) }}</p>
                    <p style="margin:0 0 8px;font-size:13px;line-height:1.6;">{{ __('Payment::ticket-email.intro.'.$event) }}</p>
                @endif
                @if(in_array($event, ['booking_confirmed', 'exchange_completed'], true))<p style="margin:0 0 8px;">{{ __('Payment::ticket-email.note') }}</p>@endif
                <p style="margin:0;">{{ __('Payment::ticket-email.footer') }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
