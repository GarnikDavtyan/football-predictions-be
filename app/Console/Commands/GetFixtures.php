<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Team;
use App\Services\FixtureService;
use App\Services\RoundService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GetFixtures extends Command
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
    protected $description = 'Update the current round and get or update the fixtures of the leagues from RapidApi';

    private $fixtureService;
    private $roundService;

    public function __construct(FixtureService $fixtureService, RoundService $roundService)
    {
        parent::__construct();

        $this->fixtureService = $fixtureService;
        $this->roundService = $roundService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::beginTransaction();

            foreach (League::all() as $league) {
                $currentRoundApi = $this->roundService->fetchCurrentRound(
                    $league->league_api_id,
                    $league->season
                );

                preg_match('/\d+/', $currentRoundApi, $matches);
                $currentRound = isset($matches[0]) ? (int)$matches[0] : $league->current_round + 1;

                $league->current_round = $currentRound;
                $league->round_api = $currentRoundApi;

                if ($currentRound > $league->rounds) {
                    $league->rounds = $currentRound;
                }

                $league->save();

                $this->getFixtures($league);
            }

            DB::commit();

            $this->info('Fixtures fetched successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error fetching fixtures: ' . $e->getMessage());
            $this->error('Failed to fetch the current round fixtures of the league: ' . $league->name);
        }
    }

    private function getFixtures(League $league)
    {
        $fixtures = $this->fixtureService->fetchFixtures($league);

        foreach ($fixtures as $fixture) {
            $teamHome = Team::where('team_api_id', $fixture->teams->home->id)->first();
            $teamAway = Team::where('team_api_id', $fixture->teams->away->id)->first();

            $fixtureDate = Carbon::parse($fixture->fixture->date)->format('Y-m-d H:i:s');

            Fixture::updateOrCreate([
                'fixture_api_id' => $fixture->fixture->id,
            ], [
                'league_id' => $league->id,
                'round' => $league->current_round,
                'date' => $fixtureDate,
                'team_home_id' => $teamHome->id,
                'team_away_id' => $teamAway->id,
                'status' => $fixture->fixture->status->short
            ]);
        }
    }
}
