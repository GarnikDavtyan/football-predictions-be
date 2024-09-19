<?php

namespace App\Http\Controllers;

use App\Helpers\UserHelper;
use App\Http\Requests\PredictionsRequest;
use App\Models\Fixture;
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
                        $fixturesQuery->where('status', 'FT');
                    } else {
                        return $this->errorResponse('No user with this name', 404);
                    }
                }
                $fixturesQuery->with(['predictions' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }]);
            }
        }

        $fixtures = $fixturesQuery->orderBy('date')->get();

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
                $x2Fixture = Fixture::findOrFail($x2FixtureId);

                Prediction::where('user_id', $userId)
                    ->whereHas('fixture', function ($query) use ($x2Fixture) {
                        $query->where('league_id', $x2Fixture->league_id)
                            ->where('round', $x2Fixture->round);
                    })
                    ->update(['x2' => false]);
            }

            foreach ($predictions as $prediction) {
                $fixtureId = $prediction['fixture_id'];

                $fixture = Fixture::findOrFail($fixtureId);
                if ($fixture->status !== 'NS' || $fixture->date <= now()) {
                    throw new Exception('cheating');
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

            if ($e->getMessage() === 'cheating') {
                return $this->errorResponse('You are cheating!', 400);
            }
            return $this->errorResponse();
        }
    }
}
