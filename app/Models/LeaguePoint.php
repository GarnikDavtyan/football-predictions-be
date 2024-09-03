<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaguePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'league_id',
        'points'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
