<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\Team;
use Illuminate\Http\JsonResponse;

class LeagueController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->successResponse(League::all());
    }

    public function getStandings(int $league_id)
    {
        $standings = Team::where('league_id', $league_id)->whereNotNull('rank')->orderBy('rank')->get();

        return $this->successResponse($standings);
    }
}
