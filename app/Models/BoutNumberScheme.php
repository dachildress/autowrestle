<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoutNumberScheme extends Model
{
    protected $table = 'bout_number_schemes';

    protected $fillable = [
        'tournament_id',
        'scheme_name',
        'start_at',
        'skip_byes',
        'match_ids',
        'all_mats',
        'all_groups',
        'all_rounds',
        'mat_numbers',
        'round_numbers',
        'same_mat_per_bracket',
    ];

    protected $casts = [
        'skip_byes' => 'boolean',
        'all_mats' => 'boolean',
        'all_groups' => 'boolean',
        'all_rounds' => 'boolean',
        'mat_numbers' => 'array',
        'round_numbers' => 'array',
        'same_mat_per_bracket' => 'boolean',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'id');
    }

    /**
     * Pivot rows: which (division_id, group_id) are in this scheme when all_groups is false.
     */
    public function schemeGroups(): HasMany
    {
        return $this->hasMany(BoutNumberSchemeGroup::class, 'bout_number_scheme_id', 'id');
    }
}
