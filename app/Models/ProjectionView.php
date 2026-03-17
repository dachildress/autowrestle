<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectionView extends Model
{
    protected $table = 'projection_views';

    protected $fillable = [
        'tournament_id',
        'name',
        'wrestlers_per_mat',
    ];

    protected $casts = [
        'wrestlers_per_mat' => 'integer',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'id');
    }

    public function projectionViewGroups(): HasMany
    {
        return $this->hasMany(ProjectionViewGroup::class, 'projection_view_id', 'id');
    }

    /**
     * Pairs of [group_id, division_id] for filtering bouts.
     *
     * @return array<int, array{0: int, 1: int}>
     */
    public function getGroupPairs(): array
    {
        return $this->projectionViewGroups()
            ->get()
            ->map(fn ($row) => [(int) $row->group_id, (int) $row->division_id])
            ->all();
    }
}
