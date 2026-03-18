<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeRequest extends Model
{
    protected $table = 'challenge_requests';

    public const STATUS_PENDING_ACCEPTANCE = 'pending_acceptance';
    public const STATUS_ACCEPTED_PENDING_DIRECTOR = 'accepted_pending_director';
    public const STATUS_DECLINED_BY_PARENT = 'declined_by_parent';
    public const STATUS_APPROVED_BY_DIRECTOR = 'approved_by_director';
    public const STATUS_DECLINED_BY_DIRECTOR = 'declined_by_director';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tournament_id',
        'challenger_tournament_wrestler_id',
        'challenged_tournament_wrestler_id',
        'challenger_user_id',
        'challenged_user_id',
        'status',
        'director_notes',
        'mat_number',
        'bout_id',
        'accepted_at',
        'director_acted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'director_acted_at' => 'datetime',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'id');
    }

    public function challengerTournamentWrestler(): BelongsTo
    {
        return $this->belongsTo(TournamentWrestler::class, 'challenger_tournament_wrestler_id', 'id');
    }

    public function challengedTournamentWrestler(): BelongsTo
    {
        return $this->belongsTo(TournamentWrestler::class, 'challenged_tournament_wrestler_id', 'id');
    }

    public function challengerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'challenger_user_id', 'id');
    }

    public function challengedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'challenged_user_id', 'id');
    }

    public function isPendingAcceptance(): bool
    {
        return $this->status === self::STATUS_PENDING_ACCEPTANCE;
    }

    public function isAcceptedPendingDirector(): bool
    {
        return $this->status === self::STATUS_ACCEPTED_PENDING_DIRECTOR;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }
}
