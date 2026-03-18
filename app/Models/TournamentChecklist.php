<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentChecklist extends Model
{
    protected $table = 'tournament_checklist';

    protected $fillable = [
        'tournament_id',
        'step_key',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'tournament_id', 'id');
    }

    /** Step keys in display order. */
    public static function stepKeys(): array
    {
        return [
            'update_tournament_info',
            'import_settings',
            'divisions_and_groups',
            'mats',
            'bout_numbering',
            'check_in',
            'bracket_divisions',
            'bout_divisions',
            'reports',
        ];
    }
}
