<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoutSetting extends Model
{
    protected $table = 'boutsettings';

    public $incrementing = true;

    protected $fillable = [
        'BoutType',
        'Round',
        'PosNumber',
        'AddTo',
    ];
}
