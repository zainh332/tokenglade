<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TkgBuyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Soneso\StellarSDK\Exceptions\HorizonRequestException;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\Transaction;

class TkgBuyController extends Controller
{
    private TkgBuyService $buyService;
    private StellarSDK $sdk;

    public function __construct(TkgBuyService $buyService)
    {
        $this->buyService = $buyService;
        $env = env('VITE_STELLAR_ENVIRONMENT', 'public');
        $this->sdk = $env === 'public'
            ? StellarSDK::getPublicNetInstance()
            : StellarSDK::getTestNetInstance();
    }

    public function meta()
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->buyService->getAssetMeta(),
        ]);
    }

    public function quote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'xlm_amount' => ['nullable', 'numeric', 'min:0.0000001'],
            'tkg_amount' => ['nullable', 'numeric', 'min:0.0000001'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $xlm = $request->filled('xlm_amount') ? (float) $request->xlm_amount : null;
        $tkg = $request->filled('tkg_amount') ? (float) $request->tkg_amount : null;

        if (($xlm === null && $tkg === null) || ($xlm !== null && $tkg !== null)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Provide exactly one of xlm_amount or tkg_amount.',
            ], 422);
        }

        try {
            $quote = $this->buyService->getQuote($xlm, $tkg);

            return response()->json([
                'status' => 'success',
                'quote' => $quote,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('[TkgBuyController@quote] ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Could not fetch quote. Try again later.',
            ], 500);
        }
    }

    public function prepare(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'public_key' => ['required', 'string', 'regex:/^(G[A-Z2-7]{55}|M[A-Z2-7]{68})$/'],
            'mode' => ['required', 'in:xlm,tkg'],
            'amount' => ['required', 'numeric', 'min:0.0000001'],
            'slippage_bps' => ['nullable', 'integer', 'min:0', 'max:2000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!$this->walletMatchesRequest($request->public_key)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wallet not connected or mismatch.',
            ], 403);
        }

        try {
            $payload = $this->buyService->buildBuyTransaction(
                $request->public_key,
                $request->mode,
                (float) $request->amount,
                (int) ($request->slippage_bps ?? 100)
            );

            return response()->json([
                'status' => 'success',
                ...$payload,
            ]);
        } catch (\InvalidArgumentException | \RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('[TkgBuyController@prepare] ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Could not prepare transaction. Try again later.',
            ], 500);
        }
    }

    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'public_key' => ['required', 'string', 'regex:/^(G[A-Z2-7]{55}|M[A-Z2-7]{68})$/'],
            'signed_xdr' => [
                'required',
                function ($attr, $value, $fail) {
                    if (!is_string($value) && !is_array($value)) {
                        $fail('signed_xdr must be a base64 string or wallet response object.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!$this->walletMatchesRequest($request->public_key)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wallet not connected or mismatch.',
            ], 403);
        }

        $signedXdr = $this->normalizeSignedXdr($request->signed_xdr);
        if (!$signedXdr) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid signed transaction payload.',
            ], 422);
        }

        try {
            $transaction = Transaction::fromEnvelopeBase64XdrString($signedXdr);
            $result = $this->sdk->submitTransaction($transaction);
            $hash = $result->getId();

            return response()->json([
                'status' => 'success',
                'message' => 'TKG purchase submitted successfully.',
                'transaction_id' => $hash,
            ]);
        } catch (HorizonRequestException $e) {
            Log::warning('[TkgBuyController@submit] Horizon error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Transaction failed on the Stellar network. Check your balance and try again.',
            ], 422);
        } catch (\Throwable $e) {
            Log::error('[TkgBuyController@submit] ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Could not submit transaction.',
            ], 500);
        }
    }

    private function walletMatchesRequest(string $publicKey): bool
    {
        $cookieKey = $_COOKIE['public_key'] ?? null;
        if ($cookieKey && $cookieKey === $publicKey) {
            return User::where('public_key', $publicKey)->where('status', 1)->exists();
        }

        return false;
    }

    private function normalizeSignedXdr($raw): ?string
    {
        if (is_array($raw)) {
            $signedXdr = $raw['signedTxXdr']
                ?? $raw['xdr']
                ?? $raw['signed_envelope_xdr']
                ?? $raw['envelope_xdr']
                ?? null;
        } else {
            $signedXdr = $raw;
        }

        if (!is_string($signedXdr)) {
            return null;
        }

        $signedXdr = trim($signedXdr);

        return base64_decode($signedXdr, true) === false ? null : $signedXdr;
    }
}
