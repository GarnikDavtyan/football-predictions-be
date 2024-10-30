<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\League;
use App\Services\FixtureService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:results';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the results of the fixtures from RapidApi';

    private $fixtureService;

    public function __construct(FixtureService $fixtureService)
    {
        parent::__construct();

        $this->fixtureService = $fixtureService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            foreach (League::all() as $league) {
                $fixtures = $this->fixtureService->fetchFixturesByStatus($league, 'FT');

                foreach ($fixtures as $fixtureResults) {
                    $fixture = Fixture::where('fixture_api_id', $fixtureResults->fixture->id)->first();

                    if ($fixture) {
                        $fixture->score_home = $fixtureResults->goals->home;
                        $fixture->score_away = $fixtureResults->goals->away;
                        $fixture->status = 'FT';

                        $fixture->save();
                    }
                }
            }

            $this->info('Results updated successfully.');
        } catch (Exception $e) {
            Log::error('Error fetching results: ' . $e->getMessage());
            $this->error('Failed to fetch the results of the league: ' . $league->name);
        }
    }
}
