<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Soneso\StellarSDK\Asset;
use Soneso\StellarSDK\ChangeTrustOperationBuilder;
use Soneso\StellarSDK\Exceptions\HorizonRequestException;
use Soneso\StellarSDK\Memo;
use Soneso\StellarSDK\Network;
use Soneso\StellarSDK\PathPaymentStrictReceiveOperationBuilder;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\TransactionBuilder;

class TkgBuyService
{
    private StellarSDK $sdk;
    private Network $network;
    private string $assetCode;
    private string $issuer;
    private string $poolId;
    private bool $isTestnet;
    private WalletService $wallet;

    private const AMM_FEE_FACTOR = 0.997;
    private const MIN_XLM = 1.0;
    private const MIN_TKG = 10.0;

    public function __construct(WalletService $wallet)
    {
        $this->wallet = $wallet;
        $env = env('VITE_STELLAR_ENVIRONMENT', 'public');
        $this->isTestnet = $env !== 'public';

        $this->sdk = $this->isTestnet
            ? StellarSDK::getTestNetInstance()
            : StellarSDK::getPublicNetInstance();

        $this->network = $this->isTestnet ? Network::testnet() : Network::public();
        $this->assetCode = env('ASSET_CODE', 'TKG');
        $this->issuer = $this->isTestnet
            ? (string) env('TKG_ISSUER_TESTNET')
            : (string) env('TKG_ISSUER_PUBLIC');
        $this->poolId = (string) env(
            'TKG_POOL_ID',
            'cb1922681c9d2380d34577d3c056e435a8436586e776c38a80412120c2442fb5'
        );
    }

    public function getAssetMeta(): array
    {
        return [
            'asset_code' => $this->assetCode,
            'issuer' => $this->issuer,
            'pool_id' => $this->poolId,
            'network' => $this->isTestnet ? 'testnet' : 'public',
            'min_xlm' => self::MIN_XLM,
            'min_tkg' => self::MIN_TKG,
        ];
    }

    public function getQuote(?float $xlmAmount = null, ?float $tkgAmount = null): array
    {
        if (!$this->issuer) {
            throw new \RuntimeException('TKG issuer is not configured.');
        }

        $reserves = $this->getPoolReserves();
        if (!$reserves) {
            throw new \RuntimeException('Could not read TKG liquidity pool.');
        }

        if ($xlmAmount !== null) {
            $xlmAmount = round($xlmAmount, 7);
            if ($xlmAmount < self::MIN_XLM) {
                throw new \InvalidArgumentException('Minimum purchase is ' . self::MIN_XLM . ' XLM.');
            }

            $estimatedTkg = $this->estimateTkgForXlm($reserves, $xlmAmount);

            return [
                'mode' => 'xlm',
                'xlm' => $xlmAmount,
                'tkg' => round($estimatedTkg, 7),
                'price_xlm_per_tkg' => $estimatedTkg > 0
                    ? round($xlmAmount / $estimatedTkg, 7)
                    : null,
                'pool_xlm' => $reserves['xlm'],
                'pool_tkg' => $reserves['tkg'],
            ];
        }

        if ($tkgAmount !== null) {
            $tkgAmount = round($tkgAmount, 7);
            if ($tkgAmount < self::MIN_TKG) {
                throw new \InvalidArgumentException('Minimum purchase is ' . self::MIN_TKG . ' TKG.');
            }

            $estimatedXlm = $this->estimateXlmForTkg($reserves, $tkgAmount);
            if ($estimatedXlm === null) {
                throw new \InvalidArgumentException('Requested TKG amount exceeds pool capacity.');
            }

            return [
                'mode' => 'tkg',
                'xlm' => round($estimatedXlm, 7),
                'tkg' => $tkgAmount,
                'price_xlm_per_tkg' => $tkgAmount > 0
                    ? round($estimatedXlm / $tkgAmount, 7)
                    : null,
                'pool_xlm' => $reserves['xlm'],
                'pool_tkg' => $reserves['tkg'],
            ];
        }

        throw new \InvalidArgumentException('Provide either xlm_amount or tkg_amount.');
    }

    public function buildBuyTransaction(
        string $publicKey,
        string $mode,
        float $amount,
        int $slippageBps = 100
    ): array {
        if (!$this->issuer) {
            throw new \RuntimeException('TKG issuer is not configured.');
        }

        $quote = $mode === 'xlm'
            ? $this->getQuote(xlmAmount: $amount)
            : $this->getQuote(tkgAmount: $amount);

        $xlmBalance = $this->wallet->getXlmBalance($publicKey);
        $slippage = max(0, min($slippageBps, 2000)) / 10000;

        try {
            $account = $this->sdk->requestAccount($publicKey);
        } catch (HorizonRequestException $e) {
            if ($e->getStatusCode() === 404) {
                throw new \RuntimeException('Stellar account does not exist or is not funded.');
            }
            throw $e;
        }

        $needsTrustline = !$this->hasTkgTrustline($account);

        if ($mode === 'xlm') {
            $sendMax = (float) $quote['xlm'];
            $destAmount = (float) $quote['tkg'] * (1 - $slippage);
        } else {
            $destAmount = (float) $quote['tkg'];
            $sendMax = (float) $quote['xlm'] * (1 + $slippage);
        }

        if ($sendMax + 0.5 > $xlmBalance) {
            throw new \RuntimeException('Not enough XLM in your wallet for this purchase (including fees).');
        }

        $sendMaxStr = number_format($sendMax, 7, '.', '');
        $destAmountStr = number_format(max($destAmount, 0.0000001), 7, '.', '');

        $tkgAsset = Asset::createNonNativeAsset($this->assetCode, $this->issuer);

        $txb = (new TransactionBuilder($account, $this->network))
            ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'TokenGlade TKG buy'));

        if ($needsTrustline) {
            $txb->addOperation(
                (new ChangeTrustOperationBuilder($tkgAsset, '922337203685.4775807'))->build()
            );
        }

        $txb->addOperation(
            (new PathPaymentStrictReceiveOperationBuilder(
                Asset::native(),
                $sendMaxStr,
                $publicKey,
                $tkgAsset,
                $destAmountStr
            ))->build()
        );

        $transaction = $txb->build();

        return [
            'unsigned_xdr' => $transaction->toEnvelopeXdrBase64(),
            'quote' => $quote,
            'needs_trustline' => $needsTrustline,
            'send_max_xlm' => $sendMaxStr,
            'min_tkg' => $destAmountStr,
            'slippage_bps' => $slippageBps,
        ];
    }

    private function hasTkgTrustline($account): bool
    {
        foreach ($account->getBalances() as $balance) {
            if (
                $balance->getAssetType() === 'credit_alphanum4' &&
                $balance->getAssetCode() === $this->assetCode &&
                $balance->getAssetIssuer() === $this->issuer
            ) {
                return true;
            }
        }

        return false;
    }

    private function estimateTkgForXlm(array $reserves, float $xlmIn): float
    {
        $xlmReserve = (float) $reserves['xlm'];
        $tkgReserve = (float) $reserves['tkg'];
        $xlmInAfterFee = $xlmIn * self::AMM_FEE_FACTOR;

        return ($tkgReserve * $xlmInAfterFee) / ($xlmReserve + $xlmInAfterFee);
    }

    private function estimateXlmForTkg(array $reserves, float $tkgOut): ?float
    {
        $xlmReserve = (float) $reserves['xlm'];
        $tkgReserve = (float) $reserves['tkg'];

        if ($tkgOut <= 0 || $tkgOut >= $tkgReserve * 0.5) {
            return null;
        }

        $effectiveTkg = $tkgOut / self::AMM_FEE_FACTOR;

        return ($xlmReserve * $effectiveTkg) / ($tkgReserve - $effectiveTkg);
    }

    private function getPoolReserves(): ?array
    {
        $base = $this->isTestnet
            ? 'https://horizon-testnet.stellar.org'
            : 'https://horizon.stellar.org';

        try {
            $response = Http::timeout(10)->acceptJson()->get($base . '/liquidity_pools/' . $this->poolId);
            if ($response->failed()) {
                Log::warning('[TkgBuyService] pool fetch failed', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $rawReserves = $response->json('reserves');
            if (!is_array($rawReserves)) {
                return null;
            }

            $xlm = null;
            $tkg = null;

            foreach ($rawReserves as $reserve) {
                $asset = $reserve['asset'] ?? null;
                $amount = $reserve['amount'] ?? null;

                if ($asset === 'native') {
                    $xlm = $amount;
                    continue;
                }

                if (!is_string($asset)) {
                    continue;
                }

                $parts = explode(':', $asset);
                if (count($parts) === 2) {
                    [$code, $issuer] = $parts;
                } elseif (count($parts) === 3) {
                    [, $code, $issuer] = $parts;
                } else {
                    continue;
                }

                if ($code === $this->assetCode && $issuer === $this->issuer) {
                    $tkg = $amount;
                }
            }

            if ($xlm === null || $tkg === null) {
                return null;
            }

            return ['xlm' => $xlm, 'tkg' => $tkg];
        } catch (\Throwable $e) {
            Log::error('[TkgBuyService] pool reserves error', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
