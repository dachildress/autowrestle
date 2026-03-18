<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentMat extends Model
{
    protected $table = 'tournament_mats';

    protected $fillable = [
        'tournament_id',
        'mat_number',
        'name',
        'constraint',
    ];

    protected $casts = [
        'mat_number' => 'integer',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'id');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->constraint
                ? $this->name . ' (' . $this->constraint . ')'
                : $this->name;
        }
        return $this->constraint
            ? 'Mat ' . $this->mat_number . ' (' . $this->constraint . ')'
            : 'Mat ' . $this->mat_number;
    }
}
