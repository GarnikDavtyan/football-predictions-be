<?php

namespace App\Console\Commands;

use App\Models\AvailableLeague;
use App\Services\ApiService;
use Illuminate\Console\Command;

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
    protected $description = 'Get leagues from the RapidAPI';

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
        $availableLeagues = AvailableLeague::all();

        \App\Models\League::truncate();

        foreach ($availableLeagues as $availableLeague) {
            $league = $this->apiService->request('leagues', [
                'name' => $availableLeague->name,
                'country' => $availableLeague->country
            ]);

            if ($league) {
                $league = $league->response[0]->league;

                \App\Models\League::create([
                    'league_id' => $league->id,
                    'name' => $league->name,
                    'logo' => $league->logo
                ]);
            } else {
                $this->error('Failed to fetch a league: ' . $availableLeague->name);
                return;
            }
        }
        
        $this->info('Leagues updated successfully.');
    }
}
