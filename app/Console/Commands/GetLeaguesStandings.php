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

class GetLeaguesStandings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:standings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the leagues standings from the RapidAPI';

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
                $standings = $this->apiService->request(ApiEndpoints::STANDINGS, [
                    'league' => $league->league_api_id,
                    'season' => Carbon::now()->year
                ]);
                
                foreach($standings->response[0]->league->standings[0] as $team) {
                    Team::where('name', $team->team->name)
                        ->update([
                            'rank' => $team->rank,
                            'games' => $team->all->played,
                            'win' => $team->all->win,
                            'draw' => $team->all->draw,
                            'lose' => $team->all->lose,
                            'goal_diff' => $team->goalsDiff,
                            'points' => $team->points,
                            'form' => $team->form
                        ]);
                }
            }

            $this->info('Standings fetched successfully.');
        } catch (Exception $e) {
            Log::error('Error fetching standings: ' . $e->getMessage());
            $this->error('Failed to fetch the standings of the league: ' . $league->name);
        }
    }
}
