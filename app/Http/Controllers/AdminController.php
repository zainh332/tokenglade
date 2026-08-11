<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StellarToken;
use App\Models\Staking;
use Illuminate\Http\Request;
use Soneso\StellarSDK\StellarSDK;

class AdminController extends Controller
{
    /**
     * Get connected wallets.
     */
    public function wallets(Request $request)
    {
        $wallets = User::orderBy('created_at', 'desc')->paginate(15);

        // Map and check balances from Stellar network (Horizon API)
        $sdk = StellarSDK::getPublicNetInstance();
        $items = collect($wallets->items())->map(function ($user) use ($sdk) {
            $balance = 0.0;
            try {
                $account = $sdk->requestAccount($user->public_key);
                if ($account) {
                    foreach ($account->getBalances() as $bal) {
                        if ($bal->getAssetType() === 'native') {
                            $balance = (float) $bal->getBalance();
                            break;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Not active or error
            }

            return [
                'id' => $user->id,
                'address' => $user->public_key,
                'created_at' => $user->created_at ? $user->created_at->toIso8601String() : null,
                'last_active' => $user->updated_at ? $user->updated_at->toIso8601String() : null,
                'balance' => $balance
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'current_page' => $wallets->currentPage(),
                'last_page' => $wallets->lastPage(),
                'total' => $wallets->total(),
                'per_page' => $wallets->perPage(),
            ]
        ]);
    }

    /**
     * Get minted tokens inventory.
     */
    public function tokens(Request $request)
    {
        $tokens = StellarToken::with('creationFeeTransaction')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $items = collect($tokens->items())->map(function ($token) {
            $feeTx = $token->creationFeeTransaction;
            return [
                'id' => $token->id,
                'code' => $token->asset_code,
                'issuer' => $token->issuer_public_key,
                'supply' => (float) $token->total_supply,
                'creator' => $token->user_wallet_address,
                'created_at' => $token->created_at ? $token->created_at->toIso8601String() : null,
                'fee_tx_hash' => $feeTx ? $feeTx->tx_hash : null,
                'fee_tx_status' => $feeTx ? (int) $feeTx->status : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'current_page' => $tokens->currentPage(),
                'last_page' => $tokens->lastPage(),
                'total' => $tokens->total(),
                'per_page' => $tokens->perPage(),
            ]
        ]);
    }

    /**
     * Get staking snapshotted stats.
     */
    public function staking(Request $request)
    {
        $stakings = Staking::with('user', 'rewards')->orderBy('created_at', 'desc')->paginate(15);

        $items = collect($stakings->items())->map(function ($stake) {
            return [
                'id' => $stake->id,
                'address' => $stake->user ? $stake->user->public_key : '—',
                'status' => $stake->is_withdrawn ? 'Withdrawn' : 'Active',
                'locked_amount' => (float) $stake->amount,
                'total_rewards' => (float) $stake->rewards->sum('reward_amount'),
                'unlock_date' => $stake->unlock_at ? $stake->unlock_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'current_page' => $stakings->currentPage(),
                'last_page' => $stakings->lastPage(),
                'total' => $stakings->total(),
                'per_page' => $stakings->perPage(),
            ]
        ]);
    }

    /**
     * Get liquidity pool participants list.
     */
    public function lpParticipants(Request $request)
    {
        $participants = \App\Models\LiquidityPoolParticipant::orderBy('pool_shares', 'desc')->paginate(15);

        $items = collect($participants->items())->map(function ($part) {
            return [
                'id' => $part->id,
                'wallet_address' => $part->wallet_address,
                'pool_shares' => (float) $part->pool_shares,
                'tkg_amount' => (float) $part->tkg_amount,
                'xlm_amount' => (float) $part->xlm_amount,
                'is_active' => $part->is_active,
                'wallet_status' => $part->wallet_status,
                'updated_at' => $part->updated_at ? $part->updated_at->toIso8601String() : null,
            ];
        });

        // Compute summary stats for the headers
        $totalTkg = \App\Models\LiquidityPoolParticipant::sum('tkg_amount');
        $totalXlm = \App\Models\LiquidityPoolParticipant::sum('xlm_amount');
        $totalShares = \App\Models\LiquidityPoolParticipant::sum('pool_shares');

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'stats' => [
                'total_tkg' => (float) $totalTkg,
                'total_xlm' => (float) $totalXlm,
                'total_shares' => (float) $totalShares,
                'total_participants' => $participants->total(),
            ],
            'meta' => [
                'current_page' => $participants->currentPage(),
                'last_page' => $participants->lastPage(),
                'total' => $participants->total(),
                'per_page' => $participants->perPage(),
            ]
        ]);
    }

    /**
     * Force sync liquidity pool participants.
     */
    public function syncLpParticipants(Request $request, \App\Services\LpSyncService $syncService)
    {
        $result = $syncService->sync();

        if ($result['status'] === 'success') {
            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'data' => $result
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $result['message']
        ], 500);
    }

    /**
     * Get settings.
     */
    public function getSettings(Request $request)
    {
        $lpWeeklyReward = \App\Models\Setting::where('key', 'lp_weekly_reward_amount')->first();
        $tokenCreationFee = \App\Models\Setting::where('key', 'token_creation_fee')->first();
        $issuerWalletAmount = \App\Models\Setting::where('key', 'issuer_wallet_amount')->first();
        $feePercentageForLp = \App\Models\Setting::where('key', 'fee_percentage_for_lp')->first();

        return response()->json([
            'status' => 'success',
            'settings' => [
                'lp_weekly_reward_amount' => $lpWeeklyReward ? (float) $lpWeeklyReward->value : 16000.0,
                'token_creation_fee' => $tokenCreationFee ? (float) $tokenCreationFee->value : (float) env('TOKEN_CREATION_FEE', 20.0),
                'issuer_wallet_amount' => $issuerWalletAmount ? (float) $issuerWalletAmount->value : 1.2,
                'fee_percentage_for_lp' => $feePercentageForLp ? (float) $feePercentageForLp->value : 0.7,
            ]
        ]);
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'lp_weekly_reward_amount' => 'nullable|numeric|min:0',
            'token_creation_fee' => 'nullable|numeric|min:0',
            'issuer_wallet_amount' => 'nullable|numeric|min:0',
            'fee_percentage_for_lp' => 'nullable|numeric|min:0|max:1',
        ]);

        if ($request->has('lp_weekly_reward_amount')) {
            \App\Models\Setting::updateOrCreate(
                ['key' => 'lp_weekly_reward_amount'],
                ['value' => $request->lp_weekly_reward_amount]
            );
        }

        if ($request->has('token_creation_fee')) {
            \App\Models\Setting::updateOrCreate(
                ['key' => 'token_creation_fee'],
                ['value' => $request->token_creation_fee]
            );
            \Illuminate\Support\Facades\Cache::forget('setting_token_creation_fee');
        }

        if ($request->has('issuer_wallet_amount')) {
            \App\Models\Setting::updateOrCreate(
                ['key' => 'issuer_wallet_amount'],
                ['value' => $request->issuer_wallet_amount]
            );
            \Illuminate\Support\Facades\Cache::forget('setting_issuer_wallet_amount');
        }

        if ($request->has('fee_percentage_for_lp')) {
            \App\Models\Setting::updateOrCreate(
                ['key' => 'fee_percentage_for_lp'],
                ['value' => $request->fee_percentage_for_lp]
            );
            \Illuminate\Support\Facades\Cache::forget('setting_fee_percentage_for_lp');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Settings updated successfully.',
        ]);
    }

    /**
     * Get LP rewards history.
     */
    public function lpHistory(Request $request)
    {
        $query = \App\Models\LpRewardDistribution::with('cycle')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('wallet_address', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('cycle_id')) {
            $query->where('lp_reward_cycle_id', $request->cycle_id);
        }

        $distributions = $query->paginate(15);

        $items = collect($distributions->items())->map(function ($dist) {
            return [
                'id' => $dist->id,
                'wallet_address' => $dist->wallet_address,
                'pool_share_percentage' => (float) $dist->pool_share_percentage,
                'reward_amount' => (float) $dist->reward_amount,
                'tx_hash' => $dist->tx_hash,
                'status' => $dist->status,
                'created_at' => $dist->created_at ? $dist->created_at->toIso8601String() : null,
                'cycle' => $dist->cycle ? [
                    'id' => $dist->cycle->id,
                    'week_number' => $dist->cycle->week_number,
                    'snapshot_date' => $dist->cycle->snapshot_date ? $dist->cycle->snapshot_date : null,
                    'total_reward_pool' => (float) $dist->cycle->total_reward_pool,
                    'status' => $dist->cycle->status,
                ] : null,
            ];
        });

        // Load all cycles for select filter
        $cycles = \App\Models\LpRewardCycle::orderBy('week_number', 'desc')->get(['id', 'week_number', 'snapshot_date']);

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'cycles' => $cycles,
            'meta' => [
                'current_page' => $distributions->currentPage(),
                'last_page' => $distributions->lastPage(),
                'total' => $distributions->total(),
                'per_page' => $distributions->perPage(),
            ]
        ]);
    }

    /**
     * Get verification payment fees / assets list.
     */
    public function getVerificationFees(Request $request)
    {
        $fees = \App\Models\VerificationPaymentAsset::orderBy('position', 'asc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $fees
        ]);
    }

    /**
     * Create or update verification fee.
     */
    public function saveVerificationFee(Request $request)
    {
        $request->validate([
            'asset_code' => 'required|string|max:12',
            'asset_issuer' => 'nullable|string|size:56',
            'amount' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'position' => 'required|integer',
        ]);

        $fee = \App\Models\VerificationPaymentAsset::updateOrCreate(
            ['id' => $request->id],
            [
                'asset_code' => $request->asset_code,
                'asset_issuer' => $request->asset_issuer,
                'amount' => $request->amount,
                'is_active' => $request->is_active,
                'position' => $request->position,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Verification fee saved successfully.',
            'data' => $fee
        ]);
    }

    /**
     * Delete verification fee.
     */
    public function deleteVerificationFee($id)
    {
        $fee = \App\Models\VerificationPaymentAsset::findOrFail($id);
        $fee->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Verification fee deleted successfully.'
        ]);
    }

    /**
     * Delete a minted token and its associated platform records.
     */
    public function deleteToken($id)
    {
        $token = \App\Models\StellarToken::findOrFail($id);

        // 1. Delete associated transactions
        \App\Models\StellarTransactions::where('stellar_token_id', $token->id)->delete();

        // 2. Delete associated token details
        \App\Models\Token::where('stellar_token_id', $token->id)->delete();

        // 3. Delete from StellarMarketToken table if it exists
        \App\Models\StellarMarketToken::where('asset_code', $token->asset_code)
            ->where('asset_issuer', $token->issuer_public_key)
            ->delete();

        // 4. Delete from StellarOhlcData table if it exists
        \App\Models\StellarOhlcData::where('asset_code', $token->asset_code)
            ->where('asset_issuer', $token->issuer_public_key)
            ->delete();

        // 5. Delete the token itself
        $token->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Token and all associated records deleted successfully.'
        ]);
    }

    /**
     * Get list of project verification claims.
     */
    public function getVerifications(Request $request)
    {
        $claims = \App\Models\VerifiedProject::where('status', '>', 0)
            ->with(['profile'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $claims->map(function ($claim) {
            $tx = \App\Models\VerificationTransaction::where('verified_project_id', $claim->id)
                ->where('status', 2)
                ->first();

            $paymentAssetCode = 'XLM';
            $paymentAssetAmount = 0;
            $paymentTxHash = '';

            if ($tx) {
                $paymentTxHash = $tx->transaction_hash;
                $paymentAssetAmount = (float) $tx->amount;
                if ($tx->verification_payment_asset_id) {
                    $asset = \App\Models\VerificationPaymentAsset::find($tx->verification_payment_asset_id);
                    if ($asset) {
                        $paymentAssetCode = $asset->asset_code;
                    }
                }
            }

            $statusStr = 'pending';
            if ($claim->status == 1) {
                $statusStr = 'approved';
            } elseif ($claim->status == 3) {
                $statusStr = 'rejected';
            }

            $profile = $claim->profile;
            $links = $profile ? $profile->officialLinks : null;
            $socials = $profile ? $profile->socialLinks : null;
            
            $wallets = collect();
            if ($profile) {
                $wallets = \App\Models\ProjectOfficialWallet::where('project_profile_id', $profile->id)->get();
            }
            if ($wallets->isEmpty() && !$profile) {
                $candidateWallets = \App\Models\ProjectOfficialWallet::where('project_profile_id', $claim->id)->get();
                if ($candidateWallets->isNotEmpty()) {
                    $profileExists = \App\Models\ProjectProfile::where('id', $claim->id)->exists();
                    if (!$profileExists) {
                        $wallets = $candidateWallets;
                    }
                }
            }

            return [
                'id' => $claim->id,
                'name' => $profile->name ?? $claim->name,
                'asset_code' => $claim->asset_code,
                'asset_issuer' => $claim->identifier,
                'sender_wallet' => $claim->wallet_address,
                'payment_asset' => $paymentAssetCode,
                'payment_amount' => $paymentAssetAmount,
                'payment_tx' => $paymentTxHash,
                'logo_url' => $profile->logo_url ?? null,
                'banner_url' => $profile->banner_url ?? null,
                'status' => $statusStr,
                'rejection_reason' => $claim->rejection_reason,
                'created_at' => $claim->created_at ? $claim->created_at->toIso8601String() : null,
                'updated_at' => $claim->updated_at ? $claim->updated_at->toIso8601String() : null,
                
                // Detailed Onboarding inputs
                'short_description' => $profile->short_description ?? '',
                'full_description' => $profile->full_description ?? '',
                'category' => $profile->category ?? '',
                'launch_date' => $profile->launch_date ?? '',
                
                'website_link' => $links->website ?? '',
                'documentation_link' => $links->documentation ?? '',
                'whitepaper_link' => $links->whitepaper ?? '',
                'github_link' => $links->github ?? '',
                'medium_link' => $links->medium ?? '',
                'official_email' => $claim->email ?? '',
                
                'twitter_link' => $socials->twitter ?? '',
                'telegram_link' => $socials->telegram ?? '',
                'discord_link' => $socials->discord ?? '',
                'linkedin_link' => $socials->linkedin ?? '',
                'reddit_link' => $socials->reddit ?? '',
                'youtube_link' => $socials->youtube ?? '',
                'tiktok_link' => $socials->tiktok ?? '',
                'instagram_link' => $socials->instagram ?? '',
                'facebook_link' => $socials->facebook ?? '',
                
                'wallets' => $wallets->map(function ($w) {
                    return [
                        'id' => $w->id,
                        'wallet_address' => $w->wallet_address,
                        'label' => $w->label,
                    ];
                })->toArray(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Update status of project verification claim.
     */
    public function updateVerificationStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:approved,rejected',
            'rejection_reason' => 'nullable|string'
        ]);

        $project = \App\Models\VerifiedProject::findOrFail($id);

        $dbStatus = 2; // pending
        if ($request->status === 'approved') {
            $dbStatus = 1;
            $project->verified_at = now();
        } elseif ($request->status === 'rejected') {
            $dbStatus = 3;
            $project->rejected_at = now();
            $project->rejection_reason = $request->rejection_reason;
        }

        $project->status = $dbStatus;
        $project->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status updated successfully.'
        ]);
    }

    /**
     * Edit the submitted verification details of a project claim.
     */
    public function editVerificationDetails(Request $request, $id)
    {
        $project = \App\Models\VerifiedProject::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'category' => 'nullable|string',
            'launch_date' => 'nullable|string',
            'short_description' => 'nullable|string',
            'full_description' => 'nullable|string',
            
            'website_link' => 'nullable|string',
            'documentation_link' => 'nullable|string',
            'whitepaper_link' => 'nullable|string',
            'github_link' => 'nullable|string',
            'medium_link' => 'nullable|string',
            
            'twitter_link' => 'nullable|string',
            'telegram_link' => 'nullable|string',
            'discord_link' => 'nullable|string',
            'linkedin_link' => 'nullable|string',
            'reddit_link' => 'nullable|string',
            'youtube_link' => 'nullable|string',
            'tiktok_link' => 'nullable|string',
            'instagram_link' => 'nullable|string',
            'facebook_link' => 'nullable|string',
            
            'official_email' => 'nullable|email',
            
            'wallets' => 'nullable|array',
            'wallets.*.wallet_address' => 'required|string',
            'wallets.*.label' => 'required|string',
            
            'logo' => 'nullable|file|image|max:2048',
            'banner' => 'nullable|file|image|max:5120',
        ]);

        $project->name = $request->name;
        $project->email = $request->official_email;
        $project->save();

        $profile = $project->profile;
        if (!$profile) {
            $profile = \App\Models\ProjectProfile::create([
                'verified_project_id' => $project->id,
                'name' => $request->name,
            ]);
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('project_logos', 'public');
            $profile->logo_url = asset('storage/' . $path);
        } elseif ($request->filled('logo_url')) {
            $profile->logo_url = $request->logo_url;
        }

        if ($request->hasFile('banner')) {
            $path = $request->file('banner')->store('project_banners', 'public');
            $profile->banner_url = asset('storage/' . $path);
        } elseif ($request->filled('banner_url')) {
            $profile->banner_url = $request->banner_url;
        }

        $profile->update([
            'name' => $request->name,
            'short_description' => $request->short_description,
            'full_description' => $request->full_description,
            'category' => $request->category,
            'launch_date' => $request->launch_date,
        ]);

        // Links
        $links = $profile->officialLinks;
        if (!$links) {
            $links = new \App\Models\ProjectOfficialLink(['project_profile_id' => $profile->id]);
        }
        $links->website = $request->website_link;
        $links->documentation = $request->documentation_link;
        $links->whitepaper = $request->whitepaper_link;
        $links->github = $request->github_link;
        $links->medium = $request->medium_link;
        $links->save();

        // Socials
        $socials = $profile->socialLinks;
        if (!$socials) {
            $socials = new \App\Models\ProjectSocialLink(['project_profile_id' => $profile->id]);
        }
        $socials->twitter = $request->twitter_link;
        $socials->telegram = $request->telegram_link;
        $socials->discord = $request->discord_link;
        $socials->linkedin = $request->linkedin_link;
        $socials->reddit = $request->reddit_link;
        $socials->youtube = $request->youtube_link;
        $socials->tiktok = $request->tiktok_link;
        $socials->instagram = $request->instagram_link;
        $socials->facebook = $request->facebook_link;
        $socials->save();

        // Wallets
        $profile->officialWallets()->delete();
        $walletsData = $request->wallets ?? [];
        foreach ($walletsData as $w) {
            \App\Models\ProjectOfficialWallet::create([
                'project_profile_id' => $profile->id,
                'wallet_address' => $w['wallet_address'],
                'label' => $w['label'],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Project details updated successfully.',
            'logo_url' => $profile->logo_url,
            'banner_url' => $profile->banner_url,
        ]);
    }

    // Project Category CRUD Actions
    public function getCategories()
    {
        return response()->json([
            'status' => 'success',
            'data' => \App\Models\ProjectCategory::orderBy('name', 'asc')->get()
        ]);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:project_categories,name'
        ]);

        $cat = \App\Models\ProjectCategory::create(['name' => $request->name]);
        return response()->json(['status' => 'success', 'data' => $cat]);
    }

    public function updateCategory(Request $request, $id)
    {
        $cat = \App\Models\ProjectCategory::findOrFail($id);
        $request->validate([
            'name' => 'required|string|unique:project_categories,name,' . $id
        ]);

        $cat->update(['name' => $request->name]);
        return response()->json(['status' => 'success', 'data' => $cat]);
    }

    public function deleteCategory($id)
    {
        $cat = \App\Models\ProjectCategory::findOrFail($id);
        $cat->delete();
        return response()->json(['status' => 'success', 'message' => 'Category deleted.']);
    }

    // Wallet Label CRUD Actions
    public function getWalletLabels()
    {
        return response()->json([
            'status' => 'success',
            'data' => \App\Models\WalletLabel::orderBy('name', 'asc')->get()
        ]);
    }

    public function storeWalletLabel(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:wallet_labels,name'
        ]);

        $label = \App\Models\WalletLabel::create(['name' => $request->name]);
        return response()->json(['status' => 'success', 'data' => $label]);
    }

    public function updateWalletLabel(Request $request, $id)
    {
        $label = \App\Models\WalletLabel::findOrFail($id);
        $request->validate([
            'name' => 'required|string|unique:wallet_labels,name,' . $id
        ]);

        $label->update(['name' => $request->name]);
        return response()->json(['status' => 'success', 'data' => $label]);
    }

    public function deleteWalletLabel($id)
    {
        $label = \App\Models\WalletLabel::findOrFail($id);
        $label->delete();
        return response()->json(['status' => 'success', 'message' => 'Wallet label deleted.']);
    }

    public function getInquiries()
    {
        $inquiries = \App\Models\ProjectInquiry::orderBy('created_at', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'inquiries' => $inquiries
        ]);
    }

    public function resolveInquiry(Request $request, $id)
    {
        $inquiry = \App\Models\ProjectInquiry::findOrFail($id);
        
        $request->validate([
            'status' => 'required|string|in:pending,resolved,ignored',
            'reply' => 'nullable|string',
        ]);

        $inquiry->update([
            'status' => $request->status,
            'reply' => $request->reply,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Inquiry updated successfully.',
            'inquiry' => $inquiry
        ]);
    }
}
