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
        'end_date',
        'link',
        'message',
        'contact_name',
        'contact_email',
        'location_name',
        'location_address',
        'city',
        'state',
        'AllowDouble',
        'status',
        'pending_approval',
        'OpenDate',
        'ViewWrestlers',
        'enable_challenge_matches',
        'usa_number_required',
        'Type',
    ];

    protected $casts = [
        'enable_challenge_matches' => 'boolean',
        'usa_number_required' => 'boolean',
        'TournamentDate' => 'date',
        'end_date' => 'date',
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

    public function tournamentMats(): HasMany
    {
        return $this->hasMany(TournamentMat::class, 'tournament_id', 'id')->orderBy('mat_number');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(TournamentChecklist::class, 'tournament_id', 'id');
    }

    /**
     * All mat numbers configured for this tournament.
     * Uses operator-defined tournament_mats when present; otherwise from division StartingMat + TotalMats.
     * Sorted ascending.
     */
    public function getConfiguredMatNumbers(): array
    {
        $defined = $this->tournamentMats()->pluck('mat_number')->all();
        if (! empty($defined)) {
            return array_values($defined);
        }
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
