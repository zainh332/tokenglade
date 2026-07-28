<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TokenStatSnapshot;
use App\Models\StellarMarketToken;
use App\Models\Token;
use App\Services\StellarTokenService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TakeTokenSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:snapshot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record hourly snapshots for active Stellar tokens and delete records older than 7 days';

    /**
     * Execute the console command.
     */
    public function handle(StellarTokenService $service)
    {
        $this->info('Starting token snapshots process...');
        
        $count = 0;
        $marketTokens = StellarMarketToken::all();

        foreach ($marketTokens as $token) {
            $code = strtoupper($token->asset_code ?? '');
            $issuer = $token->asset_issuer ?? '';

            if (!$code || !$issuer) continue;

            try {
                // Fetch current token insights dynamically from the blockchain APIs
                $insight = $service->getTokenInsight($issuer, $code);

                // Fetch liquidity pool TVL
                $xlmUsdPrice = $service->getXlmUsdPrice();
                $usdPrice = (float) ($insight['usd_price'] ?? 0);
                $liquidityInfo = $service->getLiquidityPoolsInfo($code, $issuer, $xlmUsdPrice, $usdPrice);

                $trustlines = (int) ($insight['trustlines'] ?? 0);
                $poolsCount = (int) ($insight['liquidity_pools'] ?? 0);
                $liquidityUsd = (float) ($liquidityInfo['total_tvl'] ?? 0.0);
                $circulatingSupply = (float) ($insight['total_supply'] ?? 0.0);
                $marketCapUsd = $circulatingSupply * $usdPrice;

                TokenStatSnapshot::create([
                    'asset_code'         => $code,
                    'asset_issuer'       => $issuer,
                    'holders'            => $insight['holders'] ?? $token->current_holders ?? 0,
                    'trustlines'         => $trustlines,
                    'pools_count'        => $poolsCount,
                    'liquidity_usd'      => $liquidityUsd,
                    'market_cap_usd'     => $marketCapUsd,
                    'price_usd'          => $usdPrice,
                    'circulating_supply' => $circulatingSupply,
                ]);
            } catch (\Throwable $e) {
                Log::error("Failed to fetch full stats for token {$code}: " . $e->getMessage());

                // Fallback to database values with 0s if API call fails
                TokenStatSnapshot::create([
                    'asset_code'         => $code,
                    'asset_issuer'       => $issuer,
                    'holders'            => $token->current_holders ?? 0,
                    'trustlines'         => 0,
                    'pools_count'        => 0,
                    'liquidity_usd'      => 0,
                    'market_cap_usd'     => 0,
                    'price_usd'          => $token->current_price_usd ?? 0,
                    'circulating_supply' => 0,
                ]);
            }

            $count++;
        }

        $this->info("Recorded snapshots for {$count} tokens.");

        // PURGE PURSUANT TO USER REQUIREMENT: Delete records older than 7 days
        $deleted = TokenStatSnapshot::where('created_at', '<', now()->subDays(7))->delete();
        $this->info("Purged {$deleted} historical snapshot records older than 7 days.");

        return Command::SUCCESS;
    }
}
