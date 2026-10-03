<?php

declare(strict_types=1);

namespace FuteBus\Auth\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PasswordResetOtpMail extends Mailable
{
    public function __construct(public readonly string $code) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Auth::app.password_recovery.mail.subject'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'Auth::emails.password-reset-otp');
    }
}
