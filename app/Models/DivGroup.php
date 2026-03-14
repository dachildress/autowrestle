<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DivGroup extends Model
{
    protected $table = 'divgroups';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'Name',
        'MinAge',
        'MaxAge',
        'MinGrade',
        'MaxGrade',
        'MaxWeightDiff',
        'BracketType',
        'MaxPwrDiff',
        'bracketed',
        'bouted',
        'MaxExpDiff',
        'Tournament_Id',
        'Division_id',
    ];

    protected $primaryKey = ['id', 'Tournament_Id', 'Division_id'];

    public $timestamps = true;

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class, 'Tournament_Id', 'id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'Division_id', 'id');
    }
}
