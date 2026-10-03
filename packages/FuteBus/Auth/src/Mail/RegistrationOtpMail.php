<?php

declare(strict_types=1);

namespace FuteBus\Auth\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class RegistrationOtpMail extends Mailable
{
    public function __construct(public readonly string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Auth::app.registration_flow.mail.otp_subject'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'Auth::emails.registration-otp');
    }
}
