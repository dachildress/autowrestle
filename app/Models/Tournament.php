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
        'pending_approval',
        'OpenDate',
        'ViewWrestlers',
        'Type',
    ];

    protected $casts = [
        'TournamentDate' => 'date',
        'OpenDate' => 'date',
        'pending_approval' => 'boolean',
    ];

    /**
     * Scope: tournament is in the future (or today), registration is open, and approved (visible to public).
     * Use for home page and public tournament list.
     */
    public function scopeUpcomingAndOpen($query)
    {
        $today = now()->startOfDay();
        return $query
            ->where('pending_approval', false)
            ->whereDate('TournamentDate', '>=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('OpenDate')
                    ->orWhereDate('OpenDate', '<=', $today);
            });
    }

    /** True if tournament is waiting for admin (level 0) approval. */
    public function isPendingApproval(): bool
    {
        return (bool) $this->pending_approval;
    }

    /** Mark tournament as approved (visible, registration allowed if dates allow). */
    public function approve(): void
    {
        $this->update(['pending_approval' => false]);
    }

    /** True if registration is allowed: not past tournament date and on or after open date. */
    public function isRegistrationOpen(): bool
    {
        $today = now()->startOfDay();
        if ($this->TournamentDate && $this->TournamentDate->startOfDay()->lt($today)) {
            return false;
        }
        if ($this->OpenDate && $this->OpenDate->startOfDay()->gt($today)) {
            return false;
        }
        return true;
    }

    /** True if tournament date has passed. */
    public function isPast(): bool
    {
        return $this->TournamentDate && $this->TournamentDate->startOfDay()->lt(now()->startOfDay());
    }

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
