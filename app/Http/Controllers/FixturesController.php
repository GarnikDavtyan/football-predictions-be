<?php

namespace App\Http\Controllers;

use App\Helpers\UserHelper;
use App\Http\Requests\PredictionsRequest;
use App\Models\Fixture;
use App\Models\LeaguePoint;
use App\Models\Prediction;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FixturesController extends Controller
{
    public function getFixtures(Request $request, int $leagueId, int $round): JsonResponse
    {
        $fixturesQuery = Fixture::with(['teamHome', 'teamAway'])
            ->where('league_id', $leagueId)
            ->where('round', $round);

        $userToWatchName = $request->user;
        $token = $request->bearerToken();
        if ($token) {
            $authId = UserHelper::getAuthUserId($token);
            if ($authId) {
                $userId = $authId;
                if ($userToWatchName) {
                    $userToWatch = User::where('name', $userToWatchName)->first();
                    if ($userToWatch) {
                        $userId = $userToWatch->id;
                        if ($userId !== $authId) {
                            $fixturesQuery->whereNotIn('status', ['NS', 'PST']);
                        }
                    } else {
                        return $this->errorResponse('No user with this name', 404);
                    }
                }

                $top10Users = LeaguePoint::where('league_id', $leagueId)
                    ->orderByDesc('points')
                    ->take(10)
                    ->pluck('user_id');

                $top10Users = $top10Users->reject(function ($id) use ($userId) {
                    return $id === $userId;
                })->values()->toArray();

                $fixturesQuery->with('predictions.user');
            }
        }

        $fixtures = $fixturesQuery->orderBy('date')->get();

        if (isset($userId)) {
            foreach ($fixtures as $fixture) {
                if (isset($top10Users) && !in_array($fixture->status, ['NS', 'PST'])) {
                    $fixture->top10_predictions =  $fixture->predictions
                        ->whereIn('user_id', $top10Users)
                        ->sortByDesc('points')
                        ->values()
                        ->toArray();
                }

                $fixture->prediction = $fixture->predictions->where('user_id', $userId)->first();

                $fixture->makeHidden(['predictions']);
            }
        }

        return $this->successResponse($fixtures);
    }

    public function savePredictions(PredictionsRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userId = Auth::id();
            $x2FixtureId = $request->x2_fixture_id;
            $predictions = $request->predictions;

            if ($x2FixtureId) {
                $this->validateAndClearX2($x2FixtureId, $userId);
            }

            foreach ($predictions as $prediction) {
                $fixtureId = $prediction['fixture_id'];

                $fixture = Fixture::findOrFail($fixtureId);
                if ($fixture->date <= now()) {
                    throw new Exception('CHEATING');
                }

                if (is_null($prediction['score_home']) || is_null($prediction['score_away'])) {
                    Prediction::where('user_id', $userId)->where('fixture_id', $fixtureId)->delete();
                    continue;
                }

                $prediction['x2'] = $fixtureId === $x2FixtureId ? true : false;

                $data = [
                    'user_id' => $userId,
                    'fixture_id' => $fixtureId
                ];

                Prediction::updateOrCreate($data, $prediction);
            }

            DB::commit();

            return $this->successResponse([], 'Saved successfully!');
        } catch (Exception $e) {
            DB::rollBack();

            if ($e->getMessage() === 'CHEATING') {
                return $this->errorResponse('You are cheating!', 400);
            }
            return $this->errorResponse();
        }
    }

    private function validateAndClearX2(int $x2FixtureId, int $userId)
    {
        $x2Fixture = Fixture::findOrFail($x2FixtureId);

        if ($x2Fixture->date <= now()) {
            throw new Exception('CHEATING');
        }

        $leagueRoundFixturesQuery = function ($query) use ($x2Fixture) {
            $query->where('league_id', $x2Fixture->league_id)
                ->where('round', $x2Fixture->round);
        };

        $x2Sealed = Prediction::where('user_id', $userId)
            ->whereHas('fixture', function ($query) use ($leagueRoundFixturesQuery) {
                $leagueRoundFixturesQuery($query);
                $query->where('date', '<=', now());
            })
            ->where('x2', true)
            ->exists();

        if ($x2Sealed) {
            throw new Exception('CHEATING');
        }

        Prediction::where('user_id', $userId)
            ->whereHas('fixture', $leagueRoundFixturesQuery)
            ->update(['x2' => false]);
    }
}
