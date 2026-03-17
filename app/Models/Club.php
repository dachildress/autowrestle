<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    protected $table = 'clubs';

    public $incrementing = true;

    protected $fillable = ['Club'];

    public function getNameAttribute(): string
    {
        return $this->Club;
    }
}
