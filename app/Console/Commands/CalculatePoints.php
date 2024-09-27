<?php

namespace App\Console\Commands;

use App\Helpers\PointsHelper;
use App\Models\Fixture;
use App\Models\League;
use App\Models\LeaguePoint;
use App\Models\Prediction;
use App\Models\RoundPoint;
use App\Models\User;
use App\Models\UserPoint;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CalculatePoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:points';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate user points based on the fixture results';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            foreach (League::all() as $league) {
                $leagueId = $league->id;
                $round = $league->current_round;

                $fixtures = Fixture::where('league_id', $leagueId)
                    ->where('round', $round)
                    ->where('status', 'FT')
                    ->get();

                foreach ($fixtures as $fixture) {
                    $result = $fixture->score_home . '-' . $fixture->score_away;

                    foreach ($fixture->predictions as $prediction) {
                        $userPrediction = $prediction->score_home . '-' . $prediction->score_away;
                        $points = PointsHelper::calculate($result, $userPrediction, $prediction->x2);

                        $prediction->points = $points;
                        $prediction->save();
                    }
                }

                foreach (User::all() as $user) {
                    $userId = $user->id;

                    $this->calculateRoundPoints($userId, $leagueId, $round);
                    $this->calculateLeaguePoints($userId, $leagueId);
                    $this->calculateOverallPoints($userId);
                }
            }

            $this->info('Points calculated');
        } catch (Exception $e) {
            Log::error('Error calculating points: ' . $e->getMessage());
            $this->error('Error calculating points');
        }
    }

    private function calculateRoundPoints($userId, $leagueId, $round): void
    {
        $RoundPoints = Prediction::where('user_id', $userId)
            ->whereHas('fixture', function ($query) use ($round, $leagueId) {
                $query->where('league_id', $leagueId)->where('round', $round);
            })
            ->sum('points');

        RoundPoint::updateOrCreate([
            'user_id' => $userId,
            'league_id' => $leagueId,
            'round' => $round
        ], [
            'points' => $RoundPoints
        ]);
    }

    private function calculateLeaguePoints($userId, $leagueId): void
    {
        $leaguePoints = RoundPoint::where('user_id', $userId)
            ->where('league_id', $leagueId)
            ->sum('points');

        LeaguePoint::updateOrCreate([
            'user_id' => $userId,
            'league_id' => $leagueId,
        ], [
            'points' => $leaguePoints
        ]);
    }

    private function calculateOverallPoints($userId): void
    {
        $overallPoints = LeaguePoint::where('user_id', $userId)->sum('points');

        UserPoint::updateOrCreate([
            'user_id' => $userId,
        ], [
            'points' => $overallPoints
        ]);
    }
}
