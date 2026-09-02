<?php

namespace App\Http\Controllers;

use App\Models\StellarMarketToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    private string $horizonUrl;

    public function __construct()
    {
        $this->horizonUrl = rtrim(env('HORIZON_URL', 'https://horizon.stellar.org'), '/');
    }

    /**
     * Get comprehensive transaction details, operations, and effects from Horizon.
     */
    public function show(string $hash): JsonResponse
    {
        $hash = trim(strtolower($hash));

        // Validate 64-character hex transaction hash
        if (!preg_match('/^[a-f0-9]{64}$/i', $hash)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid Stellar transaction hash format. Expected a 64-character hex string.',
            ], 400);
        }

        $cacheKey = "tx_details_{$hash}";
        $data = Cache::remember($cacheKey, 60, function () use ($hash) {
            return $this->fetchTransactionData($hash);
        });

        if (!$data) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Transaction not found on the Stellar network or failed to load.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * Fetch raw data from Horizon and enrich with tokens metadata.
     */
    private function fetchTransactionData(string $hash): ?array
    {
        try {
            // 1. Fetch transaction metadata
            $txRes = Http::timeout(6)->retry(2, 200)->get("{$this->horizonUrl}/transactions/{$hash}");
            if (!$txRes->ok()) {
                Log::warning("Horizon error fetching tx {$hash}: HTTP " . $txRes->status());
                return null;
            }

            $tx = $txRes->json();

            // 2. Fetch operations for this transaction
            $opsRes = Http::timeout(6)->retry(2, 200)->get("{$this->horizonUrl}/transactions/{$hash}/operations", [
                'limit' => 100,
                'order' => 'asc',
            ]);
            $operations = $opsRes->ok() ? ($opsRes->json()['_embedded']['records'] ?? []) : [];

            // 3. Fetch effects for this transaction
            $effectsRes = Http::timeout(6)->retry(2, 200)->get("{$this->horizonUrl}/transactions/{$hash}/effects", [
                'limit' => 100,
                'order' => 'asc',
            ]);
            $effects = $effectsRes->ok() ? ($effectsRes->json()['_embedded']['records'] ?? []) : [];

            // 4. Enrich operations with token logos and metadata
            $operations = $this->enrichOperations($operations);

            // 5. Calculate fees in XLM
            $feeChargedStroops = (int) ($tx['fee_charged'] ?? 0);
            $maxFeeStroops = (int) ($tx['max_fee'] ?? 0);
            $feeChargedXlm = $feeChargedStroops / 10000000;
            $maxFeeXlm = $maxFeeStroops / 10000000;

            // 6. Calculate transaction byte size from Envelope XDR
            $envelopeXdr = $tx['envelope_xdr'] ?? '';
            $txSizeBytes = $envelopeXdr ? strlen(base64_decode($envelopeXdr)) : 0;

            return [
                'id'                      => $tx['id'] ?? $hash,
                'hash'                    => $tx['hash'] ?? $hash,
                'successful'              => (bool) ($tx['successful'] ?? true),
                'ledger'                  => $tx['ledger'] ?? null,
                'created_at'              => $tx['created_at'] ?? null,
                'source_account'          => $tx['source_account'] ?? '',
                'source_account_sequence' => $tx['source_account_sequence'] ?? '',
                'fee_account'             => $tx['fee_account'] ?? ($tx['source_account'] ?? ''),
                'fee_charged_stroops'     => $feeChargedStroops,
                'fee_charged_xlm'         => $feeChargedXlm,
                'max_fee_stroops'         => $maxFeeStroops,
                'max_fee_xlm'             => $maxFeeXlm,
                'operation_count'         => (int) ($tx['operation_count'] ?? count($operations)),
                'memo_type'               => $tx['memo_type'] ?? 'none',
                'memo'                    => $tx['memo'] ?? null,
                'memo_bytes'              => $tx['memo_bytes'] ?? null,
                'signatures'              => $tx['signatures'] ?? [],
                'preconditions'           => $tx['preconditions'] ?? null,
                'envelope_xdr'            => $tx['envelope_xdr'] ?? '',
                'result_xdr'              => $tx['result_xdr'] ?? '',
                'result_meta_xdr'         => $tx['result_meta_xdr'] ?? ($tx['fee_meta_xdr'] ?? ''),
                'tx_size_bytes'           => $txSizeBytes,
                'operations'              => $operations,
                'effects'                 => $effects,
            ];
        } catch (\Throwable $e) {
            Log::error("Exception in TransactionController::fetchTransactionData for {$hash}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Enrich operations list with token icons and metadata.
     */
    private function enrichOperations(array $operations): array
    {
        // Extract unique asset codes + issuers to batch query database
        $assetKeys = [];
        foreach ($operations as $op) {
            $code = $op['asset_code'] ?? ($op['buying_asset_code'] ?? ($op['selling_asset_code'] ?? ''));
            $issuer = $op['asset_issuer'] ?? ($op['buying_asset_issuer'] ?? ($op['selling_asset_issuer'] ?? ''));
            if ($code && $issuer) {
                $assetKeys[] = strtoupper($code) . ':' . $issuer;
            }
        }
        $assetKeys = array_unique($assetKeys);

        $tokensMap = [];
        if (!empty($assetKeys)) {
            $tokens = StellarMarketToken::where(function ($q) use ($assetKeys) {
                foreach ($assetKeys as $k) {
                    [$code, $issuer] = explode(':', $k);
                    $q->orWhere(function ($sub) use ($code, $issuer) {
                        $sub->where('asset_code', $code)->where('asset_issuer', $issuer);
                    });
                }
            })->get();

            foreach ($tokens as $t) {
                $k = strtoupper($t->asset_code) . ':' . $t->asset_issuer;
                $tokensMap[$k] = [
                    'name'        => $t->name ?? $t->asset_code,
                    'logo'        => $t->logo ?? null,
                    'usd_price'   => (float) ($t->current_price_usd ?? 0.0),
                    'is_verified' => (bool) ($t->is_verified ?? false),
                ];
            }
        }

        foreach ($operations as &$op) {
            $code = $op['asset_code'] ?? '';
            $issuer = $op['asset_issuer'] ?? '';
            if ($code && $issuer) {
                $k = strtoupper($code) . ':' . $issuer;
                if (isset($tokensMap[$k])) {
                    $op['token_meta'] = $tokensMap[$k];
                }
            }
        }

        return $operations;
    }
}
