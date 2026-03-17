<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Live scoring state for one bout (one row per bout).
 * Identified by tournament_id + bout_id.
 */
class BoutScoringState extends Model
{
    protected $table = 'bout_scoring_state';

    protected $fillable = [
        'tournament_id',
        'bout_id',
        'red_wrestler_id',
        'green_wrestler_id',
        'red_score',
        'green_score',
        'period',
        'clock_seconds',
        'display_swap',
        'status',
        'winner_id',
        'result_type',
        'completed_at',
        'blood_time_red',
        'blood_time_green',
        'injury_time_red',
        'injury_time_green',
        'head_neck_time_red',
        'head_neck_time_green',
        'recovery_time_red',
        'recovery_time_green',
    ];

    protected $casts = [
        'red_score' => 'integer',
        'green_score' => 'integer',
        'period' => 'integer',
        'clock_seconds' => 'integer',
        'display_swap' => 'boolean',
        'blood_time_red' => 'integer',
        'blood_time_green' => 'integer',
        'injury_time_red' => 'integer',
        'injury_time_green' => 'integer',
        'head_neck_time_red' => 'integer',
        'head_neck_time_green' => 'integer',
        'recovery_time_red' => 'integer',
        'recovery_time_green' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'id');
    }

    public function redWrestler(): BelongsTo
    {
        return $this->belongsTo(TournamentWrestler::class, 'red_wrestler_id', 'id');
    }

    public function greenWrestler(): BelongsTo
    {
        return $this->belongsTo(TournamentWrestler::class, 'green_wrestler_id', 'id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(TournamentWrestler::class, 'winner_id', 'id');
    }

    public function events(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BoutScoringEvent::class, 'bout_id', 'bout_id')
            ->where('bout_scoring_events.tournament_id', $this->tournament_id);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
