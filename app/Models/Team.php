<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_api_id',
        'name',
        'logo',
        'league_id',
        'rank',
        'games',
        'win',
        'draw',
        'lose',
        'goal_diff',
        'points',
        'form'
    ];
}
