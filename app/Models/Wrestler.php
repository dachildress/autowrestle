<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wrestler extends Model
{
    protected $table = 'wrestlers';

    public $incrementing = true;

    protected $fillable = [
        'wr_first_name',
        'wr_last_name',
        'wr_gender',
        'wr_club',
        'wr_age',
        'wr_grade',
        'wr_weight',
        'wr_pr',
        'wr_dob',
        'wr_wins',
        'wr_losses',
        'wr_years',
        'usawnumber',
        'coach_name',
        'coach_phone',
        'user_id',
    ];

    protected $casts = [
        'wr_dob' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function tournamentWrestlers(): HasMany
    {
        return $this->hasMany(TournamentWrestler::class, 'Wrestler_Id', 'id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->wr_first_name . ' ' . $this->wr_last_name);
    }
}
