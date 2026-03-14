<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tournament extends Model
{
    protected $table = 'tournaments';

    public $incrementing = true;

    protected $fillable = [
        'TournamentName',
        'TournamentDate',
        'link',
        'message',
        'AllowDouble',
        'status',
        'OpenDate',
        'ViewWrestlers',
        'Type',
    ];

    protected $casts = [
        'TournamentDate' => 'date',
        'OpenDate' => 'date',
    ];

    public function tournamentType(): BelongsTo
    {
        return $this->belongsTo(TournamentType::class, 'Type', 'id');
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class, 'Tournament_Id', 'id');
    }

    public function tournamentWrestlers(): HasMany
    {
        return $this->hasMany(TournamentWrestler::class, 'Tournament_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tournamentusers', 'Tournament_id', 'User_id')
            ->withPivot('Id');
    }

    /**
     * All mat numbers configured for this tournament (from division StartingMat + TotalMats).
     * Sorted ascending. Admin can assign bouts to any of these mats, including across divisions.
     */
    public function getConfiguredMatNumbers(): array
    {
        $this->load('divisions');
        $mats = [];
        foreach ($this->divisions as $d) {
            $start = (int) $d->StartingMat;
            $total = (int) $d->TotalMats;
            for ($i = 0; $i < $total; $i++) {
                $mats[$start + $i] = true;
            }
        }
        ksort($mats);
        return array_keys($mats);
    }
}
