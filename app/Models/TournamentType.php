<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentType extends Model
{
    protected $table = 'tournamenttypes';

    public $incrementing = true;

    protected $fillable = ['Name'];

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class, 'Type', 'id');
    }
}
