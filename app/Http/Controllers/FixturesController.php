<?php

namespace App\Http\Controllers;

use App\Helpers\UserHelper;
use App\Http\Requests\PredictionsRequest;
use App\Models\Fixture;
use App\Models\Prediction;
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

        $token = $request->bearerToken();
        if ($token) {
            $authId = UserHelper::getAuthUserId($token);
            if ($authId) {
                $fixturesQuery = $fixturesQuery->with(['predictions' => function ($query) use ($authId) {
                    $query->where('user_id', $authId);
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

            $data = [
                'user_id' => Auth::id()
            ];

            foreach ($request->predictions as $prediction) {
                $fixtureId = $prediction['fixture_id'];

                $fixture = Fixture::findOrFail($fixtureId);
                if ($fixture->status !== 'NS' || $fixture->date <= now()) {
                    throw new Exception('cheating');
                }

                $data['fixture_id'] = $fixtureId;
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
