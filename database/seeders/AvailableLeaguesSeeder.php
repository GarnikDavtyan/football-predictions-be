<?php

namespace Database\Seeders;

use App\Helpers\LeaguesHelper;
use App\Models\AvailableLeague;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AvailableLeaguesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach(LeaguesHelper::AVAILABLE_LEAGUES as $league) {
            AvailableLeague::create($league);
        }
    }
}
