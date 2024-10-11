<?php

namespace App\Console\Commands;

use App\Models\DeleteAccountToken;
use App\Models\RefreshToken;
use Illuminate\Console\Command;

class ClearExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all expired tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DeleteAccountToken::where('expires_at', '<', now())->delete();
        RefreshToken::where('updated_at', '<', now()->subDays(30));

        $this->info('Expired tokens have been cleared.');
    }
}
