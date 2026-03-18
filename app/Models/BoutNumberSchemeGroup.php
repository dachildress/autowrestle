<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoutNumberSchemeGroup extends Model
{
    protected $table = 'bout_number_scheme_groups';

    protected $fillable = [
        'bout_number_scheme_id',
        'tournament_id',
        'division_id',
        'group_id',
    ];

    public function scheme(): BelongsTo
    {
        return $this->belongsTo(BoutNumberScheme::class, 'bout_number_scheme_id', 'id');
    }
}
