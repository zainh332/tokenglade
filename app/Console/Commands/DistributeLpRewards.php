<?php

namespace App\Console\Commands;

use App\Models\LpRewardCycle;
use App\Models\LpRewardDistribution;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Soneso\StellarSDK\Asset;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Memo;
use Soneso\StellarSDK\Network;
use Soneso\StellarSDK\PaymentOperationBuilder;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\TransactionBuilder;
use Soneso\StellarSDK\Exceptions\HorizonRequestException;

class DistributeLpRewards extends Command
{
    protected $signature = 'lp:distribute-rewards';
    protected $description = 'Distribute weekly LP rewards';
    private $sdk, $network, $tkgIssuer, $assetCode;
    private bool $isTestnet;

    public function __construct()
    {
        parent::__construct();

        $stellarEnv = env('VITE_STELLAR_ENVIRONMENT');
        $this->isTestnet = strtolower($stellarEnv) !== 'public';

        if ($stellarEnv === 'public') {
            $this->sdk = StellarSDK::getPublicNetInstance();
            $this->network = Network::public();
            $this->tkgIssuer = env('TKG_ISSUER_PUBLIC');
        } else {
            $this->sdk = StellarSDK::getTestNetInstance();
            $this->network = Network::testnet();
            $this->tkgIssuer = env('TKG_ISSUER_TESTNET');
        }
        $this->assetCode = env('ASSET_CODE');
    }

    public function handle()
    {
        $weekNumber = Carbon::now()->weekOfYear;
        $rewardPool = 8000;
        $minimumEligible = 0.099;

        // Prevent duplicate weekly distribution
        $existing = LpRewardCycle::where('week_number', $weekNumber)->first();

        if ($existing) {
            $this->info('Rewards already distributed for this week.');
            return;
        }

        // Fetch participants from API
        $poolId = "LDFRSITIDSOSHAGTIV35HQCW4Q22QQ3FQ3TXNQ4KQBASCIGCIQX3KX4K";

        $expertUrl = "https://api.stellar.expert/explorer/public/liquidity-pool/{$poolId}/holders";
        $response = Http::get($expertUrl);

        if (!$response->successful()) {
            $this->error('Failed to fetch LP participants');
            return;
        }

        $data = $response->json();

        $participants = collect($data['_embedded']['records'] ?? []);
        if ($participants->isEmpty()) {
            $this->error('No participants found');
            return;
        }

        // Calculate total balance
        $totalBalance = $participants->sum(function ($wallet) {
            return (float) $wallet['balance'];
        });

        // Convert to percentage
        $participants = $participants->map(function ($wallet) use ($totalBalance) {
            $balance = (float) $wallet['balance'];

            $wallet['percentage'] = $totalBalance > 0
                ? ($balance / $totalBalance) * 100
                : 0;

            return $wallet;
        });


        // Filter eligible wallets
        $eligible = $participants->filter(function ($wallet) use ($minimumEligible) {
            return $wallet['percentage'] > $minimumEligible;
        });

        $eligibleTotal = $eligible->sum('percentage');

        $sourcePublic = env('LP_REWARD_PUBLIC_WALLET');

        $cycle = LpRewardCycle::create([
            'week_number' => $weekNumber,
            'snapshot_date' => now(),
            'total_reward_pool' => $rewardPool,
            'eligible_total_percentage' => $eligibleTotal,
            'status' => 'pending',
            'memo' => "TKG LP Weekly Reward",
        ]);

        foreach ($eligible as $wallet) {
            $share = (float) $wallet['percentage'];

            $reward = round(
                $rewardPool * ($share / $eligibleTotal),
                4
            );

            // Send Stellar payment here
            $txHash = $this->sendReward(
                $wallet['account'],
                $reward,
                $cycle->memo
            );

            LpRewardDistribution::create([
                'lp_reward_cycle_id' => $cycle->id,
                'wallet_address' => $wallet['account'],
                'pool_share_percentage' => $share,
                'reward_amount' => $reward,
                'tx_hash' => $txHash,
                'status' => $txHash ? 'sent' : 'failed',
            ]);
        }

        $cycle->update([
            'status' => 'completed'
        ]);

        $this->info('LP rewards distributed successfully.');
    }

    private function sendReward($wallet, $amount, $memo)
    {
        try {
            if ($amount <= 0) {
                return false;
            }

            $sourceSecret = env('LP_REWARD_WALLET_KEY');
            $sourcePublic = env('LP_REWARD_PUBLIC_WALLET');

            $sourceKeypair = KeyPair::fromSeed($sourceSecret);
            $sourceAccount = $this->sdk->requestAccount($sourcePublic);

            // TKG Asset
            $asset = Asset::createNonNativeAsset($this->assetCode, $this->tkgIssuer);

            // Create payment
            $paymentOperation = (new PaymentOperationBuilder(
                $wallet,
                $asset,
                $amount
            ))->build();

            $transaction = (new TransactionBuilder($sourceAccount))
                ->addOperation($paymentOperation)
                ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, $memo))
                ->build();

            // Sign
            $transaction->sign($sourceKeypair, $this->network);

            // Submit
            $response = $this->sdk->submitTransaction($transaction);

            if (!$response->isSuccessful()) {
                Log::error('TX FAILED', [
                    'wallet' => $wallet,
                    'amount' => $amount,
                    'memo' => $memo,
                    'response' => $response,
                ]);

                return false;
            }

            return $response->getHash();
        } catch (\Soneso\StellarSDK\Exceptions\HorizonRequestException $e) {

            $message = $e->getMessage();

            Log::error('Horizon Error RAW', [
                'wallet' => $wallet,
                'amount' => $amount,
                'memo' => $memo,
                'message' => $message,
            ]);

            // Try to extract JSON from message
            if (preg_match('/\{.*\}/', $message, $matches)) {
                $json = json_decode($matches[0], true);

                Log::error('Parsed Horizon Error', [
                    'wallet' => $wallet,
                    'result_codes' => $json['extras']['result_codes'] ?? null,
                    'full' => $json
                ]);
            }

            return false;
        } catch (\Exception $e) {

            Log::error('Reward send failed (generic)', [
                'wallet' => $wallet,
                'amount' => $amount,
                'memo' => $memo,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
