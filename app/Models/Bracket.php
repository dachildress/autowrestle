<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bracket slot: one row per (bracket id, tournament wrestler id, position).
 * DB primary key is composite: (id, wr_Id, wr_pos).
 */
class Bracket extends Model
{
    protected $table = 'brackets';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'wr_Id',
        'wr_pos',
        'bouted',
        'Tournament_Id',
        'printed',
        'Division_Id',
    ];

    protected $casts = [
        'printed' => 'boolean',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'Tournament_Id', 'id');
    }

    public function tournamentWrestler(): BelongsTo
    {
        return $this->belongsTo(TournamentWrestler::class, 'wr_Id', 'id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'Division_Id', 'id');
    }
}
