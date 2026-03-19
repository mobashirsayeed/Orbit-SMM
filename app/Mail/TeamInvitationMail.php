<?php

namespace App\Mail;

use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TeamInvitation $invitation)
    {
    }

    public function build()
    {
        return $this->subject('You\'ve been invited to join ' . $this->invitation->tenant->name)
            ->view('emails.team_invitation')
            ->with([
                'invitation' => $this->invitation,
                'acceptUrl' => route('team.invitations.accept', $this->invitation->token),
            ]);
    }
}
