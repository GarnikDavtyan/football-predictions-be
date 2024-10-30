<?php

namespace Database\Seeders;

use App\Constants\Leagues;
use App\Models\AvailableLeague;
use Illuminate\Database\Seeder;

class AvailableLeaguesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Leagues::AVAILABLE_LEAGUES as $league) {
            $league['season'] = Leagues::SEASON;
            AvailableLeague::firstOrCreate($league);
        }
    }
}
