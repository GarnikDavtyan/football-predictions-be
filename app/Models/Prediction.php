<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'league_id',
        'round',
        'fixture_id',
        'score_home',
        'score_away',
        'x2'
    ];

    public function fixture()
    {
        return $this->belongsTo(Fixture::class);
    }
}
