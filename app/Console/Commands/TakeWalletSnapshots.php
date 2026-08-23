<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TrackedWallet;
use App\Models\WalletPortfolioSnapshot;
use App\Services\WalletIntelligenceService;
use Illuminate\Support\Facades\Log;
use Throwable;

class TakeWalletSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallets:snapshot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Capture 6-hourly portfolio value and asset snapshots for active tracked wallets';

    /**
     * Execute the console command.
     */
    public function handle(WalletIntelligenceService $service): int
    {
        $this->info('Starting wallet portfolio snapshot process...');

        // 1. Update tracking lifecycle priorities
        $this->updateTrackingLifecycles();

        // 2. Fetch active tracked wallets
        $activeWallets = TrackedWallet::where('tracking_status', 'ACTIVE')
            ->orWhere('is_connected_wallet', true)
            ->orWhere('is_watchlisted', true)
            ->orWhere('is_official_wallet', true)
            ->get();

        $this->info("Found {$activeWallets->count()} active tracked wallets.");
        $snapshotsCount = 0;

        foreach ($activeWallets as $wallet) {
            $address = $wallet->wallet_address;

            // Check if wallet requires a snapshot (approx 6 hours interval)
            $latestSnapshot = WalletPortfolioSnapshot::where('wallet_address', $address)
                ->latest('snapshot_at')
                ->first();

            $needsSnapshot = false;
            if (!$latestSnapshot) {
                $needsSnapshot = true;
            } else {
                $hoursSinceLast = now()->diffInHours($latestSnapshot->snapshot_at);
                if ($hoursSinceLast >= 6) {
                    $needsSnapshot = true;
                }
            }

            if ($needsSnapshot) {
                $this->info("Taking snapshot for wallet {$address}...");
                try {
                    // Update holdings first
                    $service->refreshHoldings($address);
                    // Take snapshot
                    $service->takeSnapshot($address);
                    // Update metrics
                    $service->updateMetrics($address);

                    $snapshotsCount++;
                } catch (Throwable $e) {
                    Log::error("Failed to take snapshot for wallet {$address}: " . $e->getMessage());
                    $this->error("Error for {$address}: " . $e->getMessage());
                }
            }
        }

        $this->info("Completed snapshot process. Captured snapshots for {$snapshotsCount} wallets.");
        return Command::SUCCESS;
    }

    /**
     * Transition wallets between ACTIVE, PASSIVE, and ARCHIVED states based on activity.
     */
    protected function updateTrackingLifecycles(): void
    {
        $this->info('Updating tracking lifecycle status for inactive wallets...');

        // 1. Move to ARCHIVED: not viewed in last 30 days and not connect/watchlist/official
        $archivedCount = TrackedWallet::where('is_connected_wallet', false)
            ->where('is_watchlisted', false)
            ->where('is_official_wallet', false)
            ->where(function ($query) {
                $query->whereNull('last_viewed_at')
                      ->orWhere('last_viewed_at', '<', now()->subDays(30));
            })
            ->where('tracking_status', '!=', 'ARCHIVED')
            ->update(['tracking_status' => 'ARCHIVED']);

        if ($archivedCount > 0) {
            $this->info("Archived {$archivedCount} stale wallets.");
        }

        // 2. Move to PASSIVE: not viewed in last 7 days (but viewed within 30 days) and not connect/watchlist/official
        $passiveCount = TrackedWallet::where('is_connected_wallet', false)
            ->where('is_watchlisted', false)
            ->where('is_official_wallet', false)
            ->where('last_viewed_at', '<', now()->subDays(7))
            ->where('last_viewed_at', '>=', now()->subDays(30))
            ->where('tracking_status', '!=', 'PASSIVE')
            ->update(['tracking_status' => 'PASSIVE']);

        if ($passiveCount > 0) {
            $this->info("Moved {$passiveCount} wallets to PASSIVE tracking.");
        }
    }
}
