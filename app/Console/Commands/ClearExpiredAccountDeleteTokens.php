<?php

namespace App\Console\Commands;

use App\Models\DeleteAccountToken;
use Illuminate\Console\Command;

class ClearExpiredAccountDeleteTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:delete-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear expired account deletion tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DeleteAccountToken::where('expires_at', '<', now())->delete();

        $this->info('Expired account deletion tokens have been cleared.');
    }
}
