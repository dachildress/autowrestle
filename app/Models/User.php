<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'accesslevel',
        'active',
        'username',
        'mycode',
        'Tournament_id',
        'mat_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'mat_number' => 'integer',
        ];
    }

    public function wrestlers(): HasMany
    {
        return $this->hasMany(Wrestler::class, 'user_id', 'id');
    }

    public function managedTournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, 'tournamentusers', 'User_id', 'Tournament_id')
            ->withPivot('Id');
    }

    /**
     * Super admin (accesslevel 0) can access all tournament backends.
     * Other users can only manage tournaments they are assigned to (tournamentusers).
     */
    public function isAdmin(): bool
    {
        return $this->accesslevel === '0';
    }

    /**
     * Scorer (mat-side) user. accesslevel '5' = scorer.
     */
    public function isScorer(): bool
    {
        return $this->accesslevel === '5';
    }
}
