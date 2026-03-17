<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bout: one row per wrestler in a match. Two rows per bout (id same, Wrestler_Id different).
 * DB primary key is composite: (id, Wrestler_Id).
 */
class Bout extends Model
{
    protected $table = 'bouts';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'Wrestler_Id',
        'Bracket_Id',
        'mat_number',
        'round',
        'points',
        'wrtime',
        'pin',
        'color',
        'scored',
        'Tournament_Id',
        'score',
        'printed',
        'Division_Id',
        'completed',
    ];

    protected $casts = [
        'points' => 'float',
        'score' => 'float',
        'printed' => 'boolean',
        'completed' => 'boolean',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'Tournament_Id', 'id');
    }

    public function tournamentWrestler(): BelongsTo
    {
        return $this->belongsTo(TournamentWrestler::class, 'Wrestler_Id', 'id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'Division_Id', 'id');
    }
}
