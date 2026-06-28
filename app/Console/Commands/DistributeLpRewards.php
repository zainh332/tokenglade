<?php

namespace App\Console\Commands;

use App\Models\LpRewardCycle;
use App\Services\LpSyncService;
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
    protected $signature = 'lp:distribute-rewards {--force : Force the distribution even if already run this week}';
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

    public function handle(LpSyncService $syncService)
    {
        $sourceSecret = env('LP_REWARD_WALLET_KEY');
        $sourcePublic = env('LP_REWARD_PUBLIC_WALLET');

        if (empty($sourceSecret) || empty($sourcePublic)) {
            $this->error('LP_REWARD_WALLET_KEY or LP_REWARD_PUBLIC_WALLET is not configured in the environment variables.');
            Log::error('LP distribution aborted: Environment variables for LP reward wallets are missing.');
            return;
        }

        try {
            $this->sdk->requestAccount($sourcePublic);
        } catch (\Throwable $e) {
            $this->error('Failed to request LP Reward source account: ' . $e->getMessage());
            Log::error('LP distribution aborted: Source account request failed.', ['error' => $e->getMessage()]);
            return;
        }

        $this->info('Syncing liquidity pool participants before reward distribution...');
        $syncService->sync();

        $weekNumber = Carbon::now()->weekOfYear;
        $rewardPoolSetting = \App\Models\Setting::where('key', 'lp_weekly_reward_amount')->first();
        $rewardPool = $rewardPoolSetting ? (float) $rewardPoolSetting->value : 16000.0;
        $minimumEligible = 0.099;

        // Prevent duplicate weekly distribution
        if (!$this->option('force')) {
            $existing = LpRewardCycle::where('week_number', $weekNumber)
                ->whereYear('snapshot_date', Carbon::now()->year)
                ->first();

            if ($existing) {
                $this->info('Rewards already distributed for this week.');
                Log::error('LP distribution aborted: Rewards already distributed for this week.', [
                    'week_number' => $weekNumber
                ]);
                return;
            }
        } else {
            $this->info('Force flag detected. Bypassing duplicate week check.');
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
            $errResponse = $e->getHorizonErrorResponse();
            $resultCodes = null;
            $rawDetail = $e->getMessage();
            
            if ($errResponse) {
                $rawDetail = $errResponse->getDetail();
                if ($errResponse->getExtras()) {
                    $extras = $errResponse->getExtras();
                    $resultCodes = [
                        'transaction' => $extras->getResultCodesTransaction(),
                        'operations' => $extras->getResultCodesOperation(),
                    ];
                }
            }

            Log::error('LP Distribute Reward Horizon Request Exception', [
                'wallet' => $wallet,
                'amount' => $amount,
                'memo' => $memo,
                'message' => $rawDetail,
                'result_codes' => $resultCodes,
                'http_status_code' => $e->getStatusCode(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('LP Distribute Reward Generic Exception', [
                'wallet' => $wallet,
                'amount' => $amount,
                'memo' => $memo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
