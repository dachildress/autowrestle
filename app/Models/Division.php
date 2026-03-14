<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $table = 'divisions';

    public $incrementing = true;

    protected $fillable = [
        'DivisionName',
        'StartingMat',
        'TotalMats',
        'PerBracket',
        'Tournament_Id',
        'bouted',
        'Bracketed',
        'printedbrackets',
        'printedbouts',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'Tournament_Id', 'id');
    }

    public function divGroups(): HasMany
    {
        return $this->hasMany(DivGroup::class, 'Division_id', 'id');
    }

    public function periodSettings(): HasMany
    {
        return $this->hasMany(DivisionPeriodSetting::class, 'division_id', 'id')->orderBy('sort_order');
    }
}
