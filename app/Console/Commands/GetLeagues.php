<?php

namespace App\Console\Commands;

use App\Models\AvailableLeague;
use App\Models\League;
use App\Services\LeagueService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    private $leagueService;

    public function __construct(LeagueService $leagueService)
    {
        parent::__construct();

        $this->leagueService = $leagueService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $availableLeagues = AvailableLeague::all();

            foreach ($availableLeagues as $availableLeague) {
                $league = $this->leagueService->fetchLeague(
                    $availableLeague->name,
                    $availableLeague->country
                );

                $leagueApiId = $league->id;
                $season = $availableLeague->season;
                $leagueName = $league->name;

                League::updateOrCreate([
                    'league_api_id' => $leagueApiId,
                ], [
                    'name' => $leagueName,
                    'slug' => Str::slug($leagueName),
                    'logo' => $league->logo,
                    'season' => $season
                ]);
            }

            $this->info('Leagues updated successfully.');
        } catch (Exception $e) {
            Log::error('Error fetching leagues: ' . $e->getMessage());
            $this->error('Failed to fetch a league: ' . $availableLeague->name);
        }
    }
}
