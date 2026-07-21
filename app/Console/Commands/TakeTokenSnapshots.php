<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TokenStatSnapshot;
use App\Models\StellarMarketToken;
use App\Models\Token;
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
    public function handle()
    {
        $this->info('Starting token snapshots process...');
        
        $count = 0;
        $marketTokens = StellarMarketToken::all();

        foreach ($marketTokens as $token) {
            $code = strtoupper($token->asset_code ?? '');
            $issuer = $token->asset_issuer ?? '';

            if (!$code || !$issuer) continue;

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

            $count++;
        }

        $this->info("Recorded snapshots for {$count} tokens.");

        // PURGE PURSUANT TO USER REQUIREMENT: Delete records older than 7 days
        $deleted = TokenStatSnapshot::where('created_at', '<', now()->subDays(7))->delete();
        $this->info("Purged {$deleted} historical snapshot records older than 7 days.");

        return Command::SUCCESS;
    }
}
