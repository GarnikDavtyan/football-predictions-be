<?php

namespace App\Http\Controllers;

use App\Helpers\UserHelper;
use App\Models\LeaguePoint;
use App\Models\RoundPoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PointsController extends Controller
{
    public function getLeagueTop(Request $request, int $leagueId, int $round): JsonResponse
    {
        $leaguePoints = $this->getTopPoints(LeaguePoint::class, $leagueId);
        $roundPoints = $this->getTopPoints(RoundPoint::class, $leagueId, $round);

        $token = $request->bearerToken();
        if ($token) {
            $authId = UserHelper::getAuthUserId($token);
            if ($authId) {
                $isUserInLeagueTop = $leaguePoints->contains('user_id', $authId);
                if (!$isUserInLeagueTop) {
                    $userPoints = $this->getAuthUserPointsAndRank(LeaguePoint::class, $authId, $leagueId);
                    if($userPoints) {
                        $leaguePoints->push($userPoints);
                    }
                }

                $isUserInRoundTop = $roundPoints->contains('user_id', $authId);
                if (!$isUserInRoundTop) {
                    $userPoints = $this->getAuthUserPointsAndRank(RoundPoint::class, $authId, $leagueId, $round);
                    if($userPoints) {
                        $roundPoints->push($userPoints);
                    }
                }
            }
        }

        return $this->successResponse(compact('leaguePoints', 'roundPoints'));
    }

    private function getTopPoints(string $model, int $leagueId, ?int $round = null): Collection
    {
        $query = $model::with('user')->where('league_id', $leagueId);

        if ($round) {
            $query->where('round', $round);
        }

        return $query->orderByDesc('points')->take(10)->get();
    }

    private function getAuthUserPointsAndRank(string $model, int $authId, int $leagueId, ?int $round = null): ?object
    {
        $userPointsQuery = $model::with('user')
                        ->where('league_id', $leagueId)
                        ->where('user_id', $authId);

        if($round) {
            $userPointsQuery = $userPointsQuery->where('round', $round);
        }

        $userPoints = $userPointsQuery->first();

        if ($userPoints) {
            $rankQuery = $model::where('league_id', $leagueId)->where('points', '>', $userPoints->points);

            if($round) {
                $rankQuery = $rankQuery->where('round', $round);
            }
            
            $rank = $rankQuery->count() + 1;

            $userPoints->rank = $rank;
        }
        return $userPoints;
    }
}
