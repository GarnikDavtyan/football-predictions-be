<?php

namespace App\Console\Commands;

use App\Models\Fixture;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateFixtureStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixtures:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of fixtures if the game has started';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Fixture::where('date', '<=', Carbon::now())
            ->where('status', 'NS')
            ->update(['status' => 'S']);

        $this->info('Fixture statuses updated.');
    }
}
