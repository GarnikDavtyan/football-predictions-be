<?php

namespace App\Console\Commands;

use App\Constants\ApiEndpoints;
use App\Models\League;
use App\Models\Team;
use App\Services\ApiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetTeams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:teams';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the teams from the RapidAPI';

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
            $leagues = League::all();

            foreach($leagues as $league) {
                $teams = $this->apiService->request(ApiEndpoints::TEAMS, [
                    'league' => $league->league_api_id,
                    'season' => Carbon::now()->year
                ]);
                
                foreach($teams->response as $team) {
                    Team::create([
                        'league_id' => $league->id,
                        'name' => $team->team->name,
                        'logo' => $team->team->logo
                    ]);
                }
            }

            $this->info('Teams fetched successfully.');
        } catch (Exception $e) {
            Log::error('Error fetching teams: ' . $e->getMessage());
            $this->error('Failed to fetch the teams of the league: ' . $league->name);
        }
    }
}
