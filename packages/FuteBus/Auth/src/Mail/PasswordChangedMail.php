<?php

declare(strict_types=1);

namespace FuteBus\Auth\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PasswordChangedMail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Auth::app.password_recovery.mail.changed_subject'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'Auth::emails.password-changed');
    }
}
