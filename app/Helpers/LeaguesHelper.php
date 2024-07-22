<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaguesHelper {
    const AVAILABLE_LEAGUES = [
        [
            'country' => 'World',
            'name' => 'UEFA Champions League'
        ],[
            'country' => 'England',
            'name' => 'Premier League'
        ],[
            'country' => 'Italy',
            'name' => 'Serie A'
        ],[
            'country' => 'Spain',
            'name' => 'La Liga'
        ],[
            'country' => 'Germany',
            'name' => 'Bundesliga'
        ],[
            'country' => 'France',
            'name' => 'Ligue 1'
        ],
    ];
}