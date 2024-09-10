<?php

namespace App\Console\Commands;

use App\Models\AvailableLeague;
use App\Models\League;
use App\Services\LeagueService;
use App\Services\RoundService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
    private $roundService;

    public function __construct(LeagueService $leagueService, RoundService $roundService)
    {
        parent::__construct();

        $this->leagueService = $leagueService;
        $this->roundService = $roundService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::table('predictions')->delete();
            DB::table('fixtures')->delete();
            DB::table('teams')->delete();
            DB::table('round_points')->delete();
            DB::table('league_points')->delete();
            DB::table('user_points')->delete();
            DB::table('leagues')->delete();

            $availableLeagues = AvailableLeague::all();

            foreach ($availableLeagues as $availableLeague) {
                $league = $this->leagueService->fetchLeague(
                    $availableLeague->name,
                    $availableLeague->country
                );

                $leagueApiId = $league->id;
                $season = $availableLeague->season;
                $leagueName = $league->name;

                $rounds = $this->roundService->fetchRounds($leagueApiId, $season);

                League::create([
                    'league_api_id' => $leagueApiId,
                    'name' => $leagueName,
                    'slug' => Str::slug($leagueName),
                    'logo' => $league->logo,
                    'rounds' => count($rounds),
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
