<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DivisionPeriodSetting extends Model
{
    protected $table = 'division_period_settings';

    protected $fillable = [
        'division_id',
        'period_code',
        'period_label',
        'sort_order',
        'duration_seconds',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'duration_seconds' => 'integer',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id', 'id');
    }
}
