<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use Illuminate\Http\JsonResponse;

class FixturesController extends Controller
{
    public function getFixtures(int $leagueId, int $round): JsonResponse
    {
        $fixtures = Fixture::with(['teamHome', 'teamAway'])
                        ->where('league_id', $leagueId)
                        ->where('round', $round)
                        ->orderBy('date')
                        ->get();

        return $this->successResponse($fixtures);
    }
}
