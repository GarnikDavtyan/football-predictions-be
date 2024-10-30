<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\Team;
use App\Services\StandingsService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetStandings extends Command
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

    private $standingsService;

    public function __construct(StandingsService $standingsService)
    {
        parent::__construct();

        $this->standingsService = $standingsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            foreach (League::all() as $league) {
                $standings = $this->standingsService->fetchStandings(
                    $league->league_api_id,
                    $league->season
                );

                foreach ($standings as $team) {
                    Team::where('team_api_id', $team->team->id)
                        ->where('league_id', $league->id)
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
