<?php

namespace App\Console\Commands;

use App\Constants\ApiEndpoints;
use App\Models\AvailableLeague;
use App\Models\League;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GetLeagues extends BaseCommand
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
                $league = $this->apiService->request(ApiEndpoints::LEAGUES, [
                    'name' => $availableLeague->name,
                    'country' => $availableLeague->country
                ]);
                $league = $league->response[0]->league;

                $leagueApiId = $league->id;
                $season = $availableLeague->season;
                $leagueName = $league->name;

                $rounds = $this->apiService->request(ApiEndpoints::ROUNDS, [
                    'league' => $leagueApiId,
                    'season' => $season
                ]);

                League::create([
                    'league_api_id' => $leagueApiId,
                    'name' => $leagueName,
                    'slug' => Str::slug($leagueName),
                    'logo' => $league->logo,
                    'rounds' => count($rounds->response),
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
