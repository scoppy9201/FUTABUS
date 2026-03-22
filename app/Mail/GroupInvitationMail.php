<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\GroupInvitation;

class GroupInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public GroupInvitation $invitation,
        public string $inviterName,
        public string $groupName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Lời mời tham gia nhóm \"{$this->groupName}\" trên Monexa",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'groups.emails.invitation',
            with: [
                'inviterName' => $this->inviterName,
                'groupName'   => $this->groupName,
                'acceptUrl'   => route('groups.invite.accept',  $this->invitation->token),
                'declineUrl'  => route('groups.invite.decline', $this->invitation->token),
                'expiresAt'   => $this->invitation->expires_at->format('d/m/Y H:i'),
            ],
        );
    }
}
