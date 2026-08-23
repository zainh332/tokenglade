<?php

namespace App\Jobs;

use App\Services\WalletIntelligenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IndexWalletHistoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $address;
    protected int $chunkCount;

    /**
     * Create a new job instance.
     */
    public function __construct(string $address, int $chunkCount = 0)
    {
        $this->address = $address;
        $this->chunkCount = $chunkCount;
    }

    /**
     * Execute the job.
     */
    public function handle(WalletIntelligenceService $service): void
    {
        @set_time_limit(120);

        // Pre-resolve custom asset logos in the background to prevent frontend API timeouts
        try {
            $holdings = \App\Models\WalletHolding::where('wallet_address', $this->address)
                ->where('asset_code', '!=', 'XLM')
                ->where('asset_type', '!=', 'liquidity_pool_shares')
                ->get();

            foreach ($holdings as $hold) {
                $key = "asset_logo_" . $hold->asset_code . "_" . $hold->asset_issuer;
                if (!\Illuminate\Support\Facades\Cache::has($key)) {
                    $res = \Illuminate\Support\Facades\Http::timeout(3)->get(
                        "https://api.stellar.expert/explorer/public/asset/{$hold->asset_code}-{$hold->asset_issuer}"
                    );
                    if ($res->successful()) {
                        $data = $res->json();
                        $logo = $data['toml_info']['image'] ?? $data['toml_info']['orgLogo'] ?? null;
                        \Illuminate\Support\Facades\Cache::put($key, $logo, 86400);
                    }
                }
            }
        } catch (\Throwable $e) {}

        $hasMore = false;
        try {
            $hasMore = $service->indexNextChunk($this->address);
        } catch (\Throwable $e) {
            $state = \App\Models\WalletIndexingState::where('wallet_address', $this->address)->first();
            if ($state) {
                $state->update(['indexing_status' => 'failed']);
            }
            \Illuminate\Support\Facades\Log::error("IndexWalletHistoryJob connection error for {$this->address}: " . $e->getMessage());
            return;
        }
        
        if ($hasMore) {
            $isSync = config('queue.default') === 'sync';
            if ($isSync) {
                // Return early on sync connection to prevent Apache/Nginx timeouts.
                // Reset status to pending so the next polling request from the frontend resumes it.
                $state = \App\Models\WalletIndexingState::where('wallet_address', $this->address)->first();
                if ($state && $state->indexing_status === 'indexing') {
                    $state->update(['indexing_status' => 'pending']);
                }
            } else if ($this->chunkCount < 4) {
                // Dispatch the next chunk with a 1 second delay
                self::dispatch($this->address, $this->chunkCount + 1)->delay(now()->addSeconds(1));
            } else {
                // We reached the limit for this thread execution.
                // Reset status to pending so that the next frontend polling request resumes it.
                $state = \App\Models\WalletIndexingState::where('wallet_address', $this->address)->first();
                if ($state && $state->indexing_status === 'indexing') {
                    $state->update(['indexing_status' => 'pending']);
                }
            }
        }
    }
}
