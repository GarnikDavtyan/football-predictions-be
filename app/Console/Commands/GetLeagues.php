<?php

namespace App\Console\Commands;

use App\Constants\ApiEndpoints;
use App\Models\AvailableLeague;
use App\Models\Fixture;
use App\Models\League;
use App\Models\Team;
use App\Services\ApiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GetLeagues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:leagues';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the leagues from the RapidAPI';

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
            $availableLeagues = AvailableLeague::all();

            DB::table('fixtures')->delete();
            DB::table('teams')->delete();
            DB::table('leagues')->delete();

            foreach ($availableLeagues as $availableLeague) {
                $league = $this->apiService->request(ApiEndpoints::LEAGUES, [
                    'name' => $availableLeague->name,
                    'country' => $availableLeague->country
                ]);
                    $league = $league->response[0]->league;

                    $rounds = $this->apiService->request(ApiEndpoints::ROUNDS, [
                        'league' => $league->id,
                        'season' => Carbon::now()->year
                    ]);

                    League::create([
                        'league_api_id' => $league->id,
                        'name' => $league->name,
                        'logo' => $league->logo,
                        'rounds' => count($rounds->response)
                    ]);
                }

                $this->info('Leagues updated successfully.');
        } catch(Exception $e) {
            Log::error('Error fetching leagues: ' . $e->getMessage());
            $this->error('Failed to fetch a league: ' . $availableLeague->name);
        }
    }
}

