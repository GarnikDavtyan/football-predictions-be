<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fixture extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixture_api_id',
        'league_id',
        'round',
        'date',
        'team_home_id',
        'team_away_id',
        'score_home',
        'score_away',
        'status'
    ];

    public function teamHome()
    {
        return $this->belongsTo(Team::class, 'team_home_id');
    }

    public function teamAway()
    {
        return $this->belongsTo(Team::class, 'team_away_id');
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class);
    }
}
