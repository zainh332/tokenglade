<?php

namespace App\Http\Controllers;

use App\Models\StellarMarketToken;
use App\Models\StellarToken;
use App\Models\WatchlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WatchlistController extends Controller
{
    /**
     * Get watchlist for a wallet or enriched list for guest items.
     */
    public function index(Request $request)
    {
        $wallet = $request->input('wallet') ?? $request->query('wallet') ?? $request->input('wallet_address') ?? $request->query('wallet_address');
        $guestItemsJson = $request->query('items') ?? $request->input('items');

        $items = [];

        if (!empty($wallet)) {
            $dbItems = WatchlistItem::where('wallet_address', $wallet)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($dbItems as $item) {
                $items[] = [
                    'id' => $item->id,
                    'asset_issuer' => $item->asset_issuer,
                    'asset_code' => $item->asset_code,
                    'created_at' => $item->created_at,
                ];
            }
        } elseif (!empty($guestItemsJson)) {
            $parsed = is_array($guestItemsJson) ? $guestItemsJson : json_decode($guestItemsJson, true);
            if (is_array($parsed)) {
                foreach ($parsed as $p) {
                    if (!empty($p['asset_issuer']) && !empty($p['asset_code'])) {
                        $items[] = [
                            'id' => null,
                            'asset_issuer' => $p['asset_issuer'],
                            'asset_code' => $p['asset_code'],
                            'created_at' => null,
                        ];
                    }
                }
            }
        }

        $enriched = $this->enrichWatchlistItems($items);

        return response()->json([
            'status' => 'success',
            'items' => $enriched,
        ]);
    }

    /**
     * Add an item to the server watchlist.
     */
    public function store(Request $request)
    {
        $wallet = $request->input('wallet') ?? $request->input('wallet_address');
        $issuer = $request->input('asset_issuer');
        $code = $request->input('asset_code');

        if (empty($wallet) || empty($issuer) || empty($code)) {
            return response()->json([
                'status' => 'error',
                'message' => 'wallet, asset_issuer and asset_code are required.',
            ], 422);
        }

        $item = WatchlistItem::firstOrCreate(
            [
                'wallet_address' => $wallet,
                'asset_issuer' => $issuer,
                'asset_code' => $code,
            ]
        );

        $enriched = $this->enrichWatchlistItems([
            [
                'id' => $item->id,
                'asset_issuer' => $item->asset_issuer,
                'asset_code' => $item->asset_code,
                'created_at' => $item->created_at,
            ]
        ]);

        return response()->json([
            'status' => 'success',
            'item' => $enriched[0] ?? $item,
        ]);
    }

    public function add(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Remove an item from watchlist.
     */
    public function destroy(Request $request, $id = null)
    {
        $wallet = $request->input('wallet') ?? $request->query('wallet') ?? $request->input('wallet_address') ?? $request->query('wallet_address');
        $issuer = $request->input('asset_issuer') ?? $request->query('asset_issuer');
        $code = $request->input('asset_code') ?? $request->query('asset_code');

        if ($id) {
            WatchlistItem::where('id', $id)->delete();
        } elseif (!empty($wallet) && !empty($issuer) && !empty($code)) {
            WatchlistItem::where('wallet_address', $wallet)
                ->where('asset_issuer', $issuer)
                ->where('asset_code', $code)
                ->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Removed from watchlist',
        ]);
    }

    /**
     * Merge/sync client-side localStorage watchlist items into server database on wallet connect.
     */
    public function sync(Request $request)
    {
        $wallet = $request->input('wallet') ?? $request->input('wallet_address');
        $items = $request->input('items', []);

        if (empty($wallet)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wallet address required for sync.',
            ], 422);
        }

        if (is_array($items)) {
            foreach ($items as $it) {
                if (!empty($it['asset_issuer']) && !empty($it['asset_code'])) {
                    WatchlistItem::firstOrCreate([
                        'wallet_address' => $wallet,
                        'asset_issuer' => $it['asset_issuer'],
                        'asset_code' => $it['asset_code'],
                    ]);
                }
            }
        }

        // Return the full updated and enriched watchlist
        $dbItems = WatchlistItem::where('wallet_address', $wallet)
            ->orderBy('created_at', 'desc')
            ->get();

        $rawList = [];
        foreach ($dbItems as $dbItem) {
            $rawList[] = [
                'id' => $dbItem->id,
                'asset_issuer' => $dbItem->asset_issuer,
                'asset_code' => $dbItem->asset_code,
                'created_at' => $dbItem->created_at,
            ];
        }

        $enriched = $this->enrichWatchlistItems($rawList);

        return response()->json([
            'status' => 'success',
            'items' => $enriched,
        ]);
    }

    /**
     * Enriches items with token name, logo, current price (USD/XLM), and 24h change.
     */
    protected function enrichWatchlistItems(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        $issuers = array_column($items, 'asset_issuer');
        $marketTokens = StellarMarketToken::whereIn('asset_issuer', $issuers)->get()->keyBy(function ($m) {
            return $m->asset_code . ':' . $m->asset_issuer;
        });

        $stellarTokens = StellarToken::whereIn('issuer_public_key', $issuers)->get()->keyBy(function ($s) {
            return $s->asset_code . ':' . $s->issuer_public_key;
        });

        $enriched = [];

        foreach ($items as $item) {
            $key = $item['asset_code'] . ':' . $item['asset_issuer'];
            $market = $marketTokens->get($key);
            $stellar = $stellarTokens->get($key);

            // Attempt to check cached insight for live price
            $cacheKey = "token_insight_v3_{$item['asset_issuer']}_{$item['asset_code']}";
            $insight = Cache::get($cacheKey);

            $usdPrice = (float)($insight['usd_price'] ?? ($market?->current_price_usd ?? 0.0));
            $xlmPrice = (float)($insight['xlm_price'] ?? ($market?->current_price_xlm ?? 0.0));
            $priceChange = (float)($insight['price_change_24h'] ?? 0.0);
            $name = $insight['name'] ?? ($market?->name ?? ($stellar?->name ?? $item['asset_code']));
            $image = $insight['image'] ?? ($market?->image ?? ($stellar?->logo ?? null));

            $enriched[] = [
                'id' => $item['id'] ?? null,
                'asset_code' => $item['asset_code'],
                'asset_issuer' => $item['asset_issuer'],
                'name' => $name,
                'image' => $image,
                'usd_price' => $usdPrice,
                'xlm_price' => $xlmPrice,
                'price_change_24h' => $priceChange,
                'is_verified' => (bool)($market?->is_verified ?? false),
                'created_at' => $item['created_at'] ?? null,
            ];
        }

        return $enriched;
    }
}
