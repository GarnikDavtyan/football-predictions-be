<?php

namespace App\Console\Commands;

use App\Constants\ApiEndpoints;
use App\Models\Fixture;
use App\Models\League;
use Exception;
use Illuminate\Support\Facades\Log;

class GetResults extends BaseCommand
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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            foreach (League::all() as $league) {
                $fixtures = $this->apiService->request(ApiEndpoints::FIXTURES, [
                    'league' => $league->league_api_id,
                    'season' => $league->season,
                    'round' => 'Regular Season - ' . $league->current_round
                ]);

                foreach ($fixtures->response as $fixtureResults) {
                    if ($fixtureResults->fixture->status->short === 'FT') {
                        $fixture = Fixture::where('fixture_api_id', $fixtureResults->fixture->id)->first();

                        if ($fixture) {
                            $fixture->score_home = $fixtureResults->goals->home;
                            $fixture->score_away = $fixtureResults->goals->away;
                            $fixture->status = 'FT';

                            $fixture->save();
                        }
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
