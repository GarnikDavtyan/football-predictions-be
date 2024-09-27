<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'avatar',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function points()
    {
        return $this->hasOne(UserPoint::class);
    }

    public function leaguePoints()
    {
        return $this->hasMany(LeaguePoint::class);
    }

    public function roundPoints()
    {
        return $this->hasMany(RoundPoint::class);
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class);
    }

    public function deleteAccountTokens()
    {
        return $this->hasMany(DeleteAccountToken::class);
    }
}
