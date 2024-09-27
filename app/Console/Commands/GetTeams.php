<?php

namespace App\Console\Commands;

use App\Models\League;
use App\Models\Team;
use App\Services\TeamService;
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

    private $teamService;

    public function __construct(TeamService $teamService)
    {
        parent::__construct();

        $this->teamService = $teamService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            foreach (League::all() as $league) {
                $teams = $this->teamService->fetchTeams(
                    $league->league_api_id,
                    $league->season
                );

                foreach ($teams as $team) {
                    Team::create([
                        'team_api_id' => $team->team->id,
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
