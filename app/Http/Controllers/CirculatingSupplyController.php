<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CirculatingSupplyController extends Controller
{
    // -----> EDIT ONLY THESE ADDRESSES
    private array $nonCirculatingWallets = [
        'staking_treasury' => [
            'GD6VHARSVOPDRQUITPOF6IPIB47RUJ4X4YZ5Q3B2D6AUNESDIUEO5XHV',
        ],
        'team' => [
            'GB3HIAGFQSF5EUPL5JWA5K5YJWY5LXTEAJP7ZMZIMR3XBQWSK3MZIUGR',
        ],
        'reserve' => [
            'GD5ZQ7XA4FKJOXPAEU2BGUPRUJ4IDZZX7BNURFNHG4TO5M7OQYPXF6NU',
        ],
        'airdrop_rewards' => [
            'GCHBC63WM2IGFODFBO6IDOD46OA4QD7NRD6457N2QMP63YWEPESWVCZF',
        ],
        'public_trading_hold' => [
            'GD534Y4JUU4CUUPKCSMO36QU5X3XKIBQAT4ZS4YLGVN62SBZIOWXBD4C',
        ],
        // 'burn_or_cold' => ['G...'],
    ];
    // <----- STOP EDITING

    /** Common HTTP options for Horizon calls */
    private array $httpOpts = [
        'timeout' => 12,
        'connect_timeout' => 6,
    ];

    public function total(Request $request)
    {
        $rid = (string) Str::uuid();
        $origin = $request->headers->get('Origin', '*');
        Log::info('[total] start', ['rid' => $rid]);

        try {
            $json = Cache::remember('tkg_total_supply', 30, function () use ($rid) {
                [$asset, $horizon] = $this->fetchAssetRecord($rid);
                return [
                    'asset_code'   => env('ASSET_CODE', 'TKG'),
                    'asset_issuer' => env('ASSET_ISSUER'),
                    'total_issued' => (string) ($asset['amount'] ?? '0'),
                    'updated_at'   => now()->toIso8601String(),
                    'source'       => $horizon,
                ];
            });

            Log::info('[total] ok', ['rid' => $rid, 'total_issued' => $json['total_issued'] ?? null]);

            return response()
                ->json($json)
                ->header('X-Request-Id', $rid)
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Cache-Control', 'public, max-age=30, s-maxage=30');
        } catch (Throwable $e) {
            Log::error('[total] fail', ['rid' => $rid, 'msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => 'Internal error. Check server logs with the request id.',
                'request_id' => $rid,
            ], 500)->header('X-Request-Id', $rid);
        }
    }

    public function show(Request $request)
    {
        $rid = (string) Str::uuid();
        $origin = $request->headers->get('Origin', '*');

        Log::info('[circ] start', [
            'rid' => $rid,
            'qs'  => $request->query(),
        ]);

        try {
            $json = Cache::remember('tkg_circulating_supply', 30, function () use ($rid, $request) {
                $code      = env('ASSET_CODE', 'TKG');
                $issuer    = env('ASSET_ISSUER');
                $excludeCB = (bool) filter_var(env('TKG_EXCLUDE_CLAIMABLE', false), FILTER_VALIDATE_BOOLEAN);

                if (!$issuer) {
                    Log::error('[circ] missing issuer', ['rid' => $rid]);
                    abort(500, 'ASSET_ISSUER missing in .env');
                }

                // 1) Asset totals
                [$asset, $horizon] = $this->fetchAssetRecord($rid);

                $balances = $asset['balances'] ?? [];
                $auth     = (float) ($balances['authorized'] ?? 0);
                $authML   = (float) ($balances['authorized_to_maintain_liabilities'] ?? 0);
                $unauth   = (float) ($balances['unauthorized'] ?? 0);


                $totalIssued = isset($asset['amount'])
                    ? (float) $asset['amount']
                    : ($auth + $authML + $unauth);

                $cbAmount  = (float) ($asset['claimable_balances_amount'] ?? 0.0);
                $lpAmount  = (float) ($asset['liquidity_pools_amount'] ?? 0.0);
                $contracts = (float) ($asset['contracts_amount'] ?? 0.0);

                // holders: prefer accounts{...}; fallback to num_accounts
                $acc        = $asset['accounts'] ?? null;
                $numHolders = is_array($acc)
                    ? (int) (($acc['authorized'] ?? 0) + ($acc['authorized_to_maintain_liabilities'] ?? 0) + ($acc['unauthorized'] ?? 0))
                    : ($asset['num_accounts'] ?? null);



                Log::debug('[circ] horizon asset', [
                    'rid' => $rid,
                    'totalIssued' => $totalIssued,
                    'cb' => $cbAmount,
                    'lp' => $lpAmount,
                    'contracts' => $contracts,
                ]);

                // 2) Non-circulating (wallets)
                $nonCircFromWallets = $this->sumNonCirculatingWallets($horizon, $code, $issuer, $rid);

                // Optional static override
                $staticOverride = (float) (env('TKG_STATIC_NON_CIRC_AMOUNT', 0));
                $nonCircTotal   = max($nonCircFromWallets, $staticOverride);
                
                // 3) Circulating
                $circulating = $totalIssued - $nonCircTotal;
                if ($excludeCB) {
                    $circulating -= $cbAmount;
                }
                $circulating = max($circulating, 0);

                $payload = [
                    'asset_code'                   => $code,
                    'asset_issuer'                 => $issuer,
                    'total_issued'                 => $this->fmt($totalIssued),
                    'excluded_non_circulating_sum' => $this->fmt($nonCircTotal),
                    'excluded_claimable_balances'  => $excludeCB ? $this->fmt($cbAmount) : '0',
                    'circulating_supply'           => $this->fmt($circulating),
                    'unit'                         => $code,
                    'updated_at'                   => now()->toIso8601String(),
                    'liquidity_pools_amount'       => $this->fmt($lpAmount),
                    'contracts_amount'             => $this->fmt($contracts),
                    'claimable_balances_amount'    => $this->fmt($cbAmount),
                    'num_holders'                  => $asset['num_accounts'] ?? null,
                    'source'                       => $horizon,
                    'methodology' => [
                        'mode'                       => $staticOverride > 0 ? 'static_or_wallet_max' : 'wallet_only',
                        'exclude_claimable'          => $excludeCB,
                        'non_circulating_categories' => array_map(fn($a) => array_values($a), $this->nonCirculatingWallets),
                    ],
                ];

                // Optional debug block (only if ?debug=1 or APP_DEBUG=true)
                $showDebug = $request->boolean('debug') || config('app.debug');
                if ($showDebug && app()->environment() !== 'production') {
                    $payload['debug'] = [
                        'request_id'      => $rid,
                        'static_override' => $this->fmt($staticOverride),
                        'wallet_sum'      => $this->fmt($nonCircFromWallets),
                        'raw_asset'       => [
                            'amount' => $asset['amount'] ?? null,
                            'claimable_balances_amount' => $asset['claimable_balances_amount'] ?? null,
                            'liquidity_pools_amount' => $asset['liquidity_pools_amount'] ?? null,
                            'contracts_amount' => $asset['contracts_amount'] ?? null,
                            'num_accounts' => $asset['num_accounts'] ?? null,
                        ],
                    ];
                }

                Log::info('[circ] ok', [
                    'rid' => $rid,
                    'circulating' => $payload['circulating_supply'],
                    'non_circ_total' => $payload['excluded_non_circulating_sum'],
                ]);

                return $payload;
            });

            return response()
                ->json($json)
                ->header('X-Request-Id', $rid)
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Cache-Control', 'public, max-age=30, s-maxage=30');
        } catch (Throwable $e) {
            Log::error('[circ] fail', [
                'rid' => $rid,
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal error. Provide this request id to the developer.',
                'request_id' => $rid,
            ], 500)->header('X-Request-Id', $rid);
        }
    }

    /** Format as plain string (no commas) to avoid consumer parsing issues */
    private function fmt(float $n): string
    {
        return rtrim(rtrim(number_format($n, 7, '.', ''), '0'), '.'); // up to 7 dp like Horizon
    }

    /**
     * Fetch /assets record for ASSET_CODE:ASSET_ISSUER with logging.
     * @return array [assetRecord, horizonUrl]
     */
    private function fetchAssetRecord(string $rid): array
    {
        $horizon = rtrim(env('HORIZON_URL', 'https://horizon.stellar.org'), '/');
        $code    = env('ASSET_CODE', 'TKG');
        $issuer  = env('ASSET_ISSUER');

        Log::debug('[horizon] assets call', ['rid' => $rid, 'url' => $horizon . '/assets', 'code' => $code, 'issuer' => $issuer]);
        try {
            $resp = Http::withOptions($this->httpOpts)
                ->retry(2, 200)
                ->get($horizon . '/assets', [
                    'asset_code'   => $code,
                    'asset_issuer' => $issuer,
                    'limit'        => 1,
                ])->throw();

            $json = $resp->json();

            // Works for both HAL and non-HAL
            $rec = $json['_embedded']['records'][0] ?? ($json['records'][0] ?? null);

            if (!$rec || !is_array($rec)) {
                Log::warning('[horizon] assets empty', [
                    'rid' => $rid,
                    'status' => $resp->status(),
                    'body' => $resp->body(),  // log FULL body so you can see what came back
                ]);
                abort(404, 'Asset not found on Horizon');
            }

            return [$rec, $horizon];
        } catch (Throwable $e) {
            Log::error('[horizon] assets error', [
                'rid' => $rid,
                'msg' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Sum balances across configured non-circulating wallets, with per-wallet logging.
     */
    private function sumNonCirculatingWallets(string $horizon, string $code, string $issuer, string $rid): float
    {
        $sum = 0.0;

        foreach ($this->nonCirculatingWallets as $category => $addresses) {
            foreach ($addresses as $acct) {
                if (!is_string($acct) || strlen($acct) !== 56 || $acct[0] !== 'G') {
                    Log::warning('[wallet] skipped malformed', ['rid' => $rid, 'category' => $category, 'acct' => $acct]);
                    continue;
                }

                try {
                    $resp = Http::withOptions($this->httpOpts)
                        ->retry(2, 200)
                        ->get($horizon . '/accounts/' . $acct);

                    if ($resp->failed()) {
                        Log::warning('[wallet] account fetch failed', [
                            'rid' => $rid,
                            'category' => $category,
                            'acct' => $acct,
                            'status' => $resp->status(),
                            'body' => $resp->body(),
                        ]);
                        continue; // don’t fail the whole calc
                    }

                    $acctRes = $resp->json();
                    $found = false;

                    foreach (($acctRes['balances'] ?? []) as $b) {
                        $type = $b['asset_type'] ?? '';
                        $isCredit = $type === 'credit_alphanum4' || $type === 'credit_alphanum12';

                        if (
                            $isCredit
                            && ($b['asset_code'] ?? '') === $code
                            && ($b['asset_issuer'] ?? '') === $issuer
                        ) {
                            $bal = (float) ($b['balance'] ?? 0);
                            $sum += $bal;
                            $found = true;

                            Log::debug('[wallet] balance', [
                                'rid' => $rid,
                                'category' => $category,
                                'acct' => $acct,
                                'balance' => $bal,
                            ]);
                            break;
                        }
                    }

                    if (!$found) {
                        Log::info('[wallet] no TKG balance', ['rid' => $rid, 'category' => $category, 'acct' => $acct]);
                    }
                } catch (Throwable $e) {
                    Log::error('[wallet] fetch error', [
                        'rid' => $rid,
                        'category' => $category,
                        'acct' => $acct,
                        'msg' => $e->getMessage(),
                    ]);
                    // continue to next wallet
                }
            }
        }

        Log::info('[wallet] non-circulating sum', ['rid' => $rid, 'sum' => $sum]);
        return $sum;
    }
}
