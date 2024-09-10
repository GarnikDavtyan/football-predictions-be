<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\League;
use App\Models\Team;
use App\Services\FixtureService;
use App\Services\RoundService;
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
    protected $description = 'Get the current round and fixtures of the leagues from RapidApi';

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
                $currentRound = $this->roundService->fetchCurrentRound(
                    $league->league_api_id,
                    $league->season,
                    'true'
                );

                preg_match('/\d+/', $currentRound, $matches);
                $currentRound = (int)$matches[0];

                if ($league->current_round !== $currentRound) {
                    $league->current_round = $currentRound;
                    $league->save();

                    $this->getFixtures($league, $currentRound);
                }
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
        $fixtures = $this->fixtureService->fetchFixtures(
            $league->league_api_id,
            $league->season,
            $round
        );

        if (!count($fixtures)) {
            throw new Exception('Fixtures are empty: ' . $league->name);
        }

        foreach ($fixtures as $fixture) {
            $teamHome = Team::where('name', $fixture->teams->home->name)->first();
            $teamAway = Team::where('name', $fixture->teams->away->name)->first();

            Fixture::create([
                'fixture_api_id' => $fixture->fixture->id,
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
