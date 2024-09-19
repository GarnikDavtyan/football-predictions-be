<?php

namespace App\Http\Controllers;

use App\Helpers\UserHelper;
use App\Models\LeaguePoint;
use App\Models\RoundPoint;
use App\Models\UserPoint;
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
                    if ($userPoints) {
                        $leaguePoints->push($userPoints);
                    }
                }

                $isUserInRoundTop = $roundPoints->contains('user_id', $authId);
                if (!$isUserInRoundTop) {
                    $userPoints = $this->getAuthUserPointsAndRank(RoundPoint::class, $authId, $leagueId, $round);
                    if ($userPoints) {
                        $roundPoints->push($userPoints);
                    }
                }
            }
        }

        return $this->successResponse(compact('leaguePoints', 'roundPoints'));
    }

    private function getTopPoints(string $model, ?int $leagueId = null, ?int $round = null): Collection
    {
        $query = $model::with('user');

        if ($leagueId) {
            $query->where('league_id', $leagueId);
        }

        if ($round) {
            $query->where('round', $round);
        }

        return $query->orderByDesc('points')->take(10)->get();
    }

    private function getAuthUserPointsAndRank(string $model, int $authId, ?int $leagueId = null, ?int $round = null): ?object
    {
        $userPointsQuery = $model::with('user')->where('user_id', $authId);

        if ($leagueId) {
            $userPointsQuery->where('league_id', $leagueId);
        }

        if ($round) {
            $userPointsQuery->where('round', $round);
        }

        $userPoints = $userPointsQuery->first();

        if ($userPoints) {
            $rankQuery = $model::where('points', '>', $userPoints->points);

            if ($round) {
                $rankQuery->where('league_id', $leagueId);
            }

            if ($round) {
                $rankQuery->where('round', $round);
            }

            $rank = $rankQuery->count() + 1;

            $userPoints->rank = $rank;
        }
        return $userPoints;
    }

    public function getTop(Request $request): JsonResponse
    {
        $top = $this->getTopPoints(UserPoint::class);

        $token = $request->bearerToken();
        if ($token) {
            $authId = UserHelper::getAuthUserId($token);
            if ($authId) {
                $isUserInTop = $top->contains('user_id', $authId);
                if (!$isUserInTop) {
                    $userPoints = $this->getAuthUserPointsAndRank(UserPoint::class, $authId);
                    if ($userPoints) {
                        $top->push($userPoints);
                    }
                }
            }
        }

        return $this->successResponse($top);
    }
}
