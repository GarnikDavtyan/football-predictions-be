<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Team;
use Illuminate\Http\JsonResponse;

class LeagueController extends Controller
{
    public function index(): JsonResponse
    {
        $leagues = League::all();

        foreach ($leagues as $league) {
            $nearestNsFixture = Fixture::where('league_id', $league->id)
                ->where('status', 'NS')
                ->orderBy('date')
                ->first();

            if ($nearestNsFixture && $nearestNsFixture->round !== $league->current_round) {
                $league['postp_round'] = $nearestNsFixture->round;
            }
        }

        return $this->successResponse($leagues);
    }

    public function getStandings(int $league_id)
    {
        $standings = Team::where('league_id', $league_id)->orderBy('rank')->get();

        return $this->successResponse($standings);
    }
}
