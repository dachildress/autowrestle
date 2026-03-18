<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentWrestler extends Model
{
    protected $table = 'tournamentwrestlers';

    public $incrementing = true;

    protected $fillable = [
        'wr_first_name',
        'wr_last_name',
        'wr_club',
        'wr_age',
        'wr_grade',
        'wr_weight',
        'group_id',
        'division_id',
        'wr_bracket_id',
        'wr_bracket_position',
        'wr_pr',
        'wr_dob',
        'wr_wins',
        'wr_losses',
        'wr_years',
        'bracketed',
        'Tournament_id',
        'Wrestler_Id',
        'checked_in',
    ];

    protected $casts = [
        'wr_dob' => 'date',
        'checked_in' => 'boolean',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'Tournament_id', 'id');
    }

    public function wrestler(): BelongsTo
    {
        return $this->belongsTo(Wrestler::class, 'Wrestler_Id', 'id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->wr_first_name . ' ' . $this->wr_last_name);
    }
}
