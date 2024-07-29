<?php

namespace App\Console\Commands;

use App\Constants\ApiEndpoints;
use App\Models\Fixture;
use App\Models\League;
use App\Models\Team;
use App\Services\ApiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GetLeaguesCurrentRoundFixtures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:fixtures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the current round and fixtures of the leagues from RapidApi';

    private $apiService;

    public function __construct(ApiService $apiService)
    {
        parent::__construct();
        $this->apiService = $apiService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::beginTransaction();
            $leagues = League::all();

            foreach($leagues as $league) {
                $this->getFixtures($league, 2);
                // $currentRound = $this->apiService->request(ApiEndpoints::ROUNDS, [
                //     'league' => $league->league_api_id,
                //     'season' => Carbon::now()->year,
                //     'current' => 'true'
                // ]);

                // preg_match('/\d+/', $currentRound->response[0], $matches);
                // $currentRound = (int)$matches[0];

                // if ($league->current_round !== $currentRound) {
                //     $league->current_round = $currentRound;
                //     $league->save();

                //     $this->getFixtures($league, $currentRound);
                // }
            }

            DB::commit();

            $this->info('Fixtures fetched successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error fetching fixtures: ' . $e->getMessage());
            $this->error('Failed to fetch the current round fixtures of the league: ' . $league->name);
        }
    }

    private function getFixtures(League $league, int $round) 
    {
        $fixtures = $this->apiService->request(ApiEndpoints::FIXTURES, [
            'league' => $league->league_api_id,
            'season' => Carbon::now()->year,
            'round' => 'Regular Season - ' . $round
        ]);

        if(!count($fixtures->response)) {
            throw new Exception('Fixtures are empty: ' . $league->name);
        }

        foreach($fixtures->response as $fixture) {
            $teamHome = Team::where('name', $fixture->teams->home->name)->first();
            $teamAway = Team::where('name', $fixture->teams->away->name)->first();

            Fixture::create([
                'fixture_id' => $fixture->fixture->id,
                'league_id' => $league->id,
                'round' => $round,
                'date' => $fixture->fixture->date,
                'team_home_id' => $teamHome->id,
                'team_away_id' => $teamAway->id,
                'status' => $fixture->fixture->status->short
            ]);
        }
    }
}
