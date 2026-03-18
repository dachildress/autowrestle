<?php

namespace App\Notifications;

use App\Models\ChallengeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChallengeRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public const TYPE_CHALLENGE_SENT = 'challenge_sent';
    public const TYPE_CHALLENGE_RECEIVED = 'challenge_received';
    public const TYPE_CHALLENGE_ACCEPTED = 'challenge_accepted';
    public const TYPE_CHALLENGE_DECLINED = 'challenge_declined';
    public const TYPE_DIRECTOR_APPROVED = 'director_approved';
    public const TYPE_DIRECTOR_DECLINED = 'director_declined';
    public const TYPE_MATCH_SCHEDULED = 'match_scheduled';

    public function __construct(
        public ChallengeRequest $challengeRequest,
        public string $type,
        public ?int $matNumber = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->challengeRequest->loadMissing(['challengerTournamentWrestler', 'challengedTournamentWrestler', 'tournament']);
        $c = $this->challengeRequest->challengerTournamentWrestler;
        $d = $this->challengeRequest->challengedTournamentWrestler;
        $challengerName = trim(($c->wr_first_name ?? '') . ' ' . ($c->wr_last_name ?? ''));
        $challengedName = trim(($d->wr_first_name ?? '') . ' ' . ($d->wr_last_name ?? ''));
        $tournamentName = $this->challengeRequest->tournament?->TournamentName ?? 'Tournament';

        $message = match ($this->type) {
            self::TYPE_CHALLENGE_SENT => "Your challenge from {$challengerName} to {$challengedName} has been sent.",
            self::TYPE_CHALLENGE_RECEIVED => "Your wrestler {$challengedName} has been challenged by {$challengerName}.",
            self::TYPE_CHALLENGE_ACCEPTED => "Challenge accepted by the other parent. Awaiting director approval.",
            self::TYPE_CHALLENGE_DECLINED => "The other parent declined the challenge.",
            self::TYPE_DIRECTOR_APPROVED => "Challenge approved by the director. Mat will be assigned soon.",
            self::TYPE_DIRECTOR_DECLINED => "The director declined the challenge.",
            self::TYPE_MATCH_SCHEDULED => $this->matNumber
                ? "Match scheduled. Report to Mat {$this->matNumber}."
                : "Match scheduled.",
            default => 'Challenge request update.',
        };

        return [
            'type' => $this->type,
            'message' => $message,
            'tournament_id' => $this->challengeRequest->tournament_id,
            'tournament_name' => $tournamentName,
            'challenge_request_id' => $this->challengeRequest->id,
            'challenger_name' => $challengerName,
            'challenged_name' => $challengedName,
            'mat_number' => $this->matNumber,
        ];
    }
}
