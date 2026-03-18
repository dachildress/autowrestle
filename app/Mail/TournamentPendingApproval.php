<?php

namespace App\Mail;

use App\Models\Tournament;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TournamentPendingApproval extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tournament $tournament
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tournament pending approval: ' . $this->tournament->TournamentName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tournament-pending-approval',
        );
    }
}
