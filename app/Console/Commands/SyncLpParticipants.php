<?php

namespace App\Console\Commands;

use App\Services\LpSyncService;
use Illuminate\Console\Command;

class SyncLpParticipants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lp:sync-participants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize liquidity pool participants and save their balances/status to the database';

    /**
     * Execute the console command.
     */
    public function handle(LpSyncService $syncService)
    {
        $this->info('Starting LP participants sync...');
        $result = $syncService->sync();

        if ($result['status'] === 'success') {
            $this->info($result['message']);
            $this->info("Synced count: " . $result['synced_count']);
            $this->info("Total pool shares: " . $result['total_pool_shares']);
            $this->info("Total TKG reserve: " . $result['total_tkg_reserve']);
            $this->info("Total XLM reserve: " . $result['total_xlm_reserve']);
        } else {
            $this->error($result['message']);
        }
    }
}
