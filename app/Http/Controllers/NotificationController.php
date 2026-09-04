<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List notifications for a wallet and return unread count.
     */
    public function index(Request $request)
    {
        $wallet = $request->input('wallet') ?? $request->query('wallet') ?? $request->input('wallet_address') ?? $request->query('wallet_address');

        if (empty($wallet)) {
            return response()->json([
                'status' => 'success',
                'unread_count' => 0,
                'notifications' => [],
            ]);
        }

        $notifications = Notification::where('wallet_address', $wallet)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $issuers = $notifications->pluck('asset_issuer')->filter()->unique()->toArray();
        $marketTokens = \App\Models\StellarMarketToken::whereIn('asset_issuer', $issuers)->get()->keyBy(function ($m) {
            return $m->asset_code . ':' . $m->asset_issuer;
        });
        $stellarTokens = \App\Models\StellarToken::whereIn('issuer_public_key', $issuers)->get()->keyBy(function ($s) {
            return $s->asset_code . ':' . $s->issuer_public_key;
        });

        $enriched = [];
        foreach ($notifications as $n) {
            $key = $n->asset_code . ':' . $n->asset_issuer;
            $market = $marketTokens->get($key);
            $stellar = $stellarTokens->get($key);
            $image = $market?->image ?? ($stellar?->logo ?? null);

            $arr = $n->toArray();
            $arr['token_image'] = $image;
            $arr['image'] = $image;
            $arr['logo'] = $image;
            $enriched[] = $arr;
        }

        $unreadCount = Notification::where('wallet_address', $wallet)
            ->where('read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'unread_count' => $unreadCount,
            'notifications' => $enriched,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead($id)
    {
        Notification::where('id', $id)->update(['read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications for a wallet as read.
     */
    public function markAllAsRead(Request $request)
    {
        $wallet = $request->input('wallet') ?? $request->query('wallet') ?? $request->input('wallet_address') ?? $request->query('wallet_address');

        if (!empty($wallet)) {
            Notification::where('wallet_address', $wallet)
                ->where('read', false)
                ->update(['read' => true]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read',
        ]);
    }
}
