<?php

declare(strict_types=1);

namespace FuteBus\Auth\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AccountActivatedMail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Auth::app.registration_flow.mail.activated_subject'));
    }

    public function content(): Content
    {
        return new Content(markdown: 'Auth::emails.account-activated');
    }
}
