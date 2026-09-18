<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MultisigTransaction;
use App\Services\StellarMultisigService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class MultisigController extends Controller
{
    protected StellarMultisigService $multisigService;

    public function __construct(StellarMultisigService $multisigService)
    {
        $this->multisigService = $multisigService;
    }

    /**
     * Get account multisig configuration and signers from Stellar Horizon
     */
    public function account(string $address): JsonResponse
    {
        try {
            $data = $this->multisigService->getAccountMultisigInfo($address);
            return response()->json([
                'status' => 'success',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Build unsigned SetOptions XDR to add, edit, or remove a signer
     */
    public function buildSignerXdr(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|string',
            'signer_public_key' => 'required|string',
            'weight' => 'required|integer|min:0|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $built = $this->multisigService->buildSetSignerXdr(
                $request->input('account_id'),
                $request->input('signer_public_key'),
                (int) $request->input('weight')
            );

            return response()->json([
                'status' => 'success',
                'data' => $built,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Build unsigned SetOptions XDR to update thresholds and master key weight
     */
    public function buildThresholdsXdr(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|string',
            'master_weight' => 'nullable|integer|min:0|max:255',
            'low' => 'nullable|integer|min:0|max:255',
            'med' => 'nullable|integer|min:0|max:255',
            'high' => 'nullable|integer|min:0|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $built = $this->multisigService->buildSetThresholdsXdr(
                $request->input('account_id'),
                $request->has('master_weight') ? (int) $request->input('master_weight') : null,
                $request->has('low') ? (int) $request->input('low') : null,
                $request->has('med') ? (int) $request->input('med') : null,
                $request->has('high') ? (int) $request->input('high') : null
            );

            return response()->json([
                'status' => 'success',
                'data' => $built,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Build unsigned multisig payment XDR
     */
    public function buildPaymentXdr(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'source_account' => 'required|string',
            'destination' => 'required|string',
            'amount' => 'required|numeric|min:0.0000001',
            'asset_code' => 'nullable|string|max:12',
            'asset_issuer' => 'nullable|string',
            'memo' => 'nullable|string|max:28',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $built = $this->multisigService->buildMultisigPaymentXdr(
                $request->input('source_account'),
                $request->input('destination'),
                $request->input('asset_code', 'XLM') ?? 'XLM',
                $request->input('asset_issuer'),
                (string) $request->input('amount'),
                $request->input('memo')
            );

            return response()->json([
                'status' => 'success',
                'data' => $built,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Decode and inspect any transaction XDR
     */
    public function inspectXdr(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'xdr' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $inspected = $this->multisigService->parseAndInspectXdr($request->input('xdr'));
            return response()->json([
                'status' => 'success',
                'data' => $inspected,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Propose and persist a multisig transaction
     */
    public function createTransaction(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|string',
            'xdr' => 'required|string',
            'created_by' => 'required|string',
            'title' => 'nullable|string|max:255',
            'operation_type' => 'nullable|string|max:32',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $accountId = $request->input('account_id');
            $xdr = $request->input('xdr');
            $createdBy = $request->input('created_by');

            // Verify and inspect signatures
            $inspected = $this->multisigService->parseAndInspectXdr($xdr);
            $verification = $this->multisigService->verifyTransactionSignatures($xdr, $accountId);

            $status = $verification['is_ready']
                ? MultisigTransaction::STATUS_READY
                : MultisigTransaction::STATUS_PENDING;

            $title = $request->input('title') ?: ($inspected['operations'][0]['details']['type'] ?? 'Multisig Transaction');

            $multisigTx = MultisigTransaction::create([
                'account_id' => $accountId,
                'title' => $title,
                'operation_type' => $request->input('operation_type') ?: ($inspected['operations'][0]['details']['type'] ?? 'custom'),
                'threshold_type' => $verification['threshold_category'],
                'required_weight' => $verification['required_weight'],
                'current_weight' => $verification['current_weight'],
                'unsigned_xdr' => $xdr,
                'signed_xdr' => $xdr,
                'signatures_json' => $verification['verified_signers'],
                'transaction_hash' => $inspected['transaction_hash'],
                'status' => $status,
                'network' => $this->multisigService->getNetworkName(),
                'created_by' => $createdBy,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $multisigTx,
                'verification' => $verification,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * List multisig transactions for an account or user
     */
    public function listTransactions(Request $request): JsonResponse
    {
        $accountId = $request->query('account_id');
        $status = $request->query('status');

        $query = MultisigTransaction::query()
            ->orderBy('created_at', 'desc');

        if ($accountId) {
            $query->where(function ($q) use ($accountId) {
                $q->where('account_id', $accountId)
                  ->orWhere('created_by', $accountId);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $transactions = $query->limit(50)->get();

        return response()->json([
            'status' => 'success',
            'data' => $transactions,
        ]);
    }

    /**
     * Get details of a multisig transaction
     */
    public function showTransaction(int $id): JsonResponse
    {
        $tx = MultisigTransaction::find($id);
        if (!$tx) {
            return response()->json([
                'status' => 'error',
                'message' => 'Multisig transaction not found.',
            ], 404);
        }

        try {
            $inspected = $this->multisigService->parseAndInspectXdr($tx->signed_xdr);
            $verification = $this->multisigService->verifyTransactionSignatures($tx->signed_xdr, $tx->account_id);

            return response()->json([
                'status' => 'success',
                'data' => $tx,
                'inspected' => $inspected,
                'verification' => $verification,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Co-sign: Add signature to an existing multisig transaction
     */
    public function addSignature(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'signed_xdr' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $tx = MultisigTransaction::find($id);
        if (!$tx) {
            return response()->json(['status' => 'error', 'message' => 'Transaction not found.'], 404);
        }

        if ($tx->status === MultisigTransaction::STATUS_SUBMITTED) {
            return response()->json(['status' => 'error', 'message' => 'Transaction has already been submitted to Stellar.'], 400);
        }

        try {
            $incomingXdr = $request->input('signed_xdr');
            $mergedXdr = $this->multisigService->mergeTransactionSignatures($tx->signed_xdr, $incomingXdr);

            $verification = $this->multisigService->verifyTransactionSignatures($mergedXdr, $tx->account_id);

            $tx->signed_xdr = $mergedXdr;
            $tx->current_weight = $verification['current_weight'];
            $tx->signatures_json = $verification['verified_signers'];

            if ($verification['is_ready']) {
                $tx->status = MultisigTransaction::STATUS_READY;
            }

            $tx->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Signature appended successfully!',
                'data' => $tx,
                'verification' => $verification,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Submit a fully signed transaction to Stellar Horizon
     */
    public function submit(Request $request, int $id): JsonResponse
    {
        $tx = MultisigTransaction::find($id);
        if (!$tx) {
            return response()->json(['status' => 'error', 'message' => 'Transaction not found.'], 404);
        }

        if ($tx->status === MultisigTransaction::STATUS_SUBMITTED) {
            return response()->json([
                'status' => 'success',
                'message' => 'Transaction has already been submitted.',
                'hash' => $tx->stellar_tx_hash,
            ]);
        }

        try {
            // Optional: accept final signed XDR override if submitted directly from wallet
            $xdrToSubmit = $request->input('signed_xdr') ?: $tx->signed_xdr;

            $result = $this->multisigService->submitTransaction($xdrToSubmit);

            if ($result['status'] === 'success') {
                $tx->status = MultisigTransaction::STATUS_SUBMITTED;
                $tx->stellar_tx_hash = $result['hash'];
                $tx->submitted_at = now();
                $tx->error_message = null;
                $tx->save();

                return response()->json([
                    'status' => 'success',
                    'message' => $result['message'],
                    'hash' => $result['hash'],
                    'ledger' => $result['ledger'] ?? null,
                    'data' => $tx,
                ]);
            } else {
                $tx->status = MultisigTransaction::STATUS_FAILED;
                $tx->error_message = $result['message'];
                $tx->save();

                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'],
                    'tx_code' => $result['tx_code'] ?? null,
                    'op_codes' => $result['op_codes'] ?? null,
                ], 400);
            }
        } catch (\Throwable $e) {
            $tx->status = MultisigTransaction::STATUS_FAILED;
            $tx->error_message = $e->getMessage();
            $tx->save();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
