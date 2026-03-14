<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One scoring event or comment during a bout (audit trail and summary).
 */
class BoutScoringEvent extends Model
{
    protected $table = 'bout_scoring_events';

    protected $fillable = [
        'tournament_id',
        'bout_id',
        'side',
        'event_type',
        'points',
        'period',
        'match_time_snapshot',
        'note',
        'created_by',
    ];

    protected $casts = [
        'points' => 'integer',
        'period' => 'integer',
        'match_time_snapshot' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
