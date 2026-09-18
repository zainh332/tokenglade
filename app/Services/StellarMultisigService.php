<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Soneso\StellarSDK\AbstractTransaction;
use Soneso\StellarSDK\Asset;
use Soneso\StellarSDK\AssetTypeCreditAlphaNum12;
use Soneso\StellarSDK\AssetTypeCreditAlphaNum4;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Crypto\StrKey;
use Soneso\StellarSDK\Memo;
use Soneso\StellarSDK\Network;
use Soneso\StellarSDK\PaymentOperation;
use Soneso\StellarSDK\PaymentOperationBuilder;
use Soneso\StellarSDK\SetOptionsOperation;
use Soneso\StellarSDK\SetOptionsOperationBuilder;
use Soneso\StellarSDK\Signer;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\Transaction;
use Soneso\StellarSDK\TransactionBuilder;
use Soneso\StellarSDK\Xdr\XdrDecoratedSignature;

class StellarMultisigService
{
    protected StellarSDK $sdk;
    protected Network $network;
    protected string $networkName;
    protected bool $isTestnet;
    protected string $horizonUrl;

    public function __construct()
    {
        $stellarEnv = env('VITE_STELLAR_ENVIRONMENT', 'public');
        $this->isTestnet = strtolower($stellarEnv) !== 'public';
        $this->networkName = $this->isTestnet ? 'testnet' : 'public';

        if ($this->isTestnet) {
            $this->sdk = StellarSDK::getTestNetInstance();
            $this->network = Network::testnet();
            $this->horizonUrl = 'https://horizon-testnet.stellar.org';
        } else {
            $this->sdk = StellarSDK::getPublicNetInstance();
            $this->network = Network::public();
            $this->horizonUrl = 'https://horizon.stellar.org';
        }
    }

    public function getNetworkName(): string
    {
        return $this->networkName;
    }

    public function isTestnet(): bool
    {
        return $this->isTestnet;
    }

    /**
     * Validates that a string is a valid Stellar Ed25519 public key (G...)
     */
    public function isValidStellarAddress(string $address): bool
    {
        if (strlen($address) !== 56 || $address[0] !== 'G') {
            return false;
        }

        try {
            $decoded = StrKey::decodeAccountId($address);
            return strlen($decoded) === 32;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Retrieves the current on-chain multisig configuration for an account
     */
    public function getAccountMultisigInfo(string $accountId): array
    {
        if (!$this->isValidStellarAddress($accountId)) {
            throw new Exception("Invalid Stellar account address: {$accountId}");
        }

        try {
            $account = $this->sdk->requestAccount($accountId);
        } catch (\Throwable $e) {
            throw new Exception("Account not found or inactive on the Stellar network.");
        }

        $thresholds = $account->getThresholds();
        $lowThreshold = $thresholds->getLowThreshold();
        $medThreshold = $thresholds->getMedThreshold();
        $highThreshold = $thresholds->getHighThreshold();

        $signers = [];
        $masterKeyWeight = 0;
        $totalWeight = 0;

        foreach ($account->getSigners() as $s) {
            $key = $s->getKey();
            $type = $s->getType();
            $weight = $s->getWeight();
            $isMaster = ($key === $accountId);

            if ($isMaster) {
                $masterKeyWeight = $weight;
            }

            $signers[] = [
                'key' => $key,
                'short_key' => substr($key, 0, 4) . '...' . substr($key, -4),
                'type' => $type,
                'weight' => $weight,
                'is_master' => $isMaster,
                'sponsor' => $s->getSponsor(),
            ];

            $totalWeight += $weight;
        }

        // Sort: master first, then descending by weight
        usort($signers, function ($a, $b) {
            if ($a['is_master'] && !$b['is_master']) return -1;
            if (!$a['is_master'] && $b['is_master']) return 1;
            return $b['weight'] <=> $a['weight'];
        });

        // Compute minimum balance / reserve impact
        // Formula: (2 + subentries) * baseReserve (0.5 XLM)
        $subentryCount = $account->getSubentryCount();
        $baseReserve = 0.5; // Stellar standard base reserve
        $minReserveXlm = (2 + $subentryCount) * $baseReserve;

        // Current XLM balance
        $xlmBalance = 0.0;
        foreach ($account->getBalances() as $b) {
            if ($b->getAssetType() === 'native') {
                $xlmBalance = (float) $b->getBalance();
                break;
            }
        }
        $availableBalance = max(0.0, $xlmBalance - $minReserveXlm);

        // Friendly description
        $isMultisig = count($signers) > 1 || $medThreshold > 1 || $highThreshold > 1;
        $summary = 'Single Signature Account';
        if ($isMultisig) {
            $summary = "Multisig ({$medThreshold} weight required for payments, {$highThreshold} for account changes)";
        }

        return [
            'account_id' => $accountId,
            'network' => $this->networkName,
            'is_multisig' => $isMultisig,
            'summary' => $summary,
            'sequence_number' => (string) $account->getSequenceNumber(),
            'master_key_weight' => $masterKeyWeight,
            'thresholds' => [
                'low' => $lowThreshold,
                'med' => $medThreshold,
                'high' => $highThreshold,
            ],
            'signers' => $signers,
            'total_signers_count' => count($signers),
            'total_available_weight' => $totalWeight,
            'subentry_count' => $subentryCount,
            'minimum_reserve_xlm' => $minReserveXlm,
            'xlm_balance' => $xlmBalance,
            'available_xlm_balance' => $availableBalance,
            'reserve_cost_per_signer_xlm' => $baseReserve,
        ];
    }

    /**
     * Builds an unsigned transaction to add, edit, or remove a signer.
     * Weight = 0 removes the signer.
     */
    public function buildSetSignerXdr(string $accountId, string $signerPublicKey, int $weight): array
    {
        if (!$this->isValidStellarAddress($accountId)) {
            throw new Exception("Invalid source account address.");
        }
        if (!$this->isValidStellarAddress($signerPublicKey)) {
            throw new Exception("Invalid signer public key. Must be a valid Ed25519 Stellar address.");
        }
        if ($weight < 0 || $weight > 255) {
            throw new Exception("Signer weight must be between 0 and 255.");
        }

        $accountInfo = $this->getAccountMultisigInfo($accountId);
        $account = $this->sdk->requestAccount($accountId);

        // Prevent setting master key as an additional signer
        if ($accountId === $signerPublicKey && $weight > 0) {
            throw new Exception("Cannot add the account itself as an additional signer. Use threshold settings to change master key weight.");
        }

        // Lockout check: if removing a signer or reducing weight, ensure thresholds can still be met
        $this->validateSignerWeightChangeSafety($accountInfo, $signerPublicKey, $weight);

        $signerKeyPair = KeyPair::fromAccountId($signerPublicKey);
        $signerKey = Signer::ed25519PublicKey($signerKeyPair);

        $memoText = $weight === 0
            ? 'Multisig: Remove Signer'
            : ($this->hasSigner($accountInfo['signers'], $signerPublicKey) ? 'Multisig: Edit Signer' : 'Multisig: Add Signer');

        $setOptionsOp = (new SetOptionsOperationBuilder())
            ->setSigner($signerKey, $weight)
            ->setSourceAccount($accountId)
            ->build();

        $tx = (new TransactionBuilder($account, $this->network))
            ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, substr($memoText, 0, 28)))
            ->addOperation($setOptionsOp)
            ->build();

        $unsignedXdr = $tx->toEnvelopeXdrBase64();

        return [
            'unsigned_xdr' => $unsignedXdr,
            'transaction_hash' => bin2hex($tx->hash($this->network)),
            'operation_type' => 'set_options',
            'threshold_type' => 'high',
            'required_weight' => $accountInfo['thresholds']['high'],
            'current_weight' => 0,
            'network' => $this->networkName,
            'title' => $weight === 0
                ? "Remove Signer " . substr($signerPublicKey, 0, 4) . '...' . substr($signerPublicKey, -4)
                : "Configure Signer " . substr($signerPublicKey, 0, 4) . '...' . substr($signerPublicKey, -4) . " (Weight: {$weight})",
        ];
    }

    /**
     * Builds an unsigned transaction to update account thresholds and master key weight
     */
    public function buildSetThresholdsXdr(
        string $accountId,
        ?int $masterWeight = null,
        ?int $low = null,
        ?int $med = null,
        ?int $high = null
    ): array {
        if (!$this->isValidStellarAddress($accountId)) {
            throw new Exception("Invalid source account address.");
        }

        $accountInfo = $this->getAccountMultisigInfo($accountId);
        $account = $this->sdk->requestAccount($accountId);

        $targetMaster = $masterWeight ?? $accountInfo['master_key_weight'];
        $targetLow = $low ?? $accountInfo['thresholds']['low'];
        $targetMed = $med ?? $accountInfo['thresholds']['med'];
        $targetHigh = $high ?? $accountInfo['thresholds']['high'];

        // Validate range
        foreach (['masterWeight' => $targetMaster, 'low' => $targetLow, 'med' => $targetMed, 'high' => $targetHigh] as $k => $v) {
            if ($v < 0 || $v > 255) {
                throw new Exception("{$k} must be between 0 and 255.");
            }
        }

        // Validate threshold relationships
        if ($targetLow > $targetMed) {
            throw new Exception("Low threshold ({$targetLow}) cannot exceed medium threshold ({$targetMed}).");
        }
        if ($targetMed > $targetHigh) {
            throw new Exception("Medium threshold ({$targetMed}) cannot exceed high threshold ({$targetHigh}).");
        }

        // Calculate future total weight
        $totalWeight = $targetMaster;
        foreach ($accountInfo['signers'] as $s) {
            if (!$s['is_master']) {
                $totalWeight += $s['weight'];
            }
        }

        // Lockout check: thresholds cannot exceed total available weight
        if ($targetHigh > $totalWeight) {
            throw new Exception("High threshold ({$targetHigh}) exceeds the total available signing weight ({$totalWeight}). This would lock the account permanently!");
        }
        if ($targetMed > $totalWeight) {
            throw new Exception("Medium threshold ({$targetMed}) exceeds the total available signing weight ({$totalWeight}).");
        }
        if ($targetMaster === 0 && count($accountInfo['signers']) <= 1) {
            throw new Exception("Cannot set master key weight to 0 without adding other signers first. Doing so would lock the account forever.");
        }

        $builder = (new SetOptionsOperationBuilder())->setSourceAccount($accountId);

        if ($masterWeight !== null) {
            $builder->setMasterKeyWeight($masterWeight);
        }
        if ($low !== null) {
            $builder->setLowThreshold($low);
        }
        if ($med !== null) {
            $builder->setMediumThreshold($med);
        }
        if ($high !== null) {
            $builder->setHighThreshold($high);
        }

        $setOptionsOp = $builder->build();

        $tx = (new TransactionBuilder($account, $this->network))
            ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'Multisig: Set Thresholds'))
            ->addOperation($setOptionsOp)
            ->build();

        $unsignedXdr = $tx->toEnvelopeXdrBase64();

        return [
            'unsigned_xdr' => $unsignedXdr,
            'transaction_hash' => bin2hex($tx->hash($this->network)),
            'operation_type' => 'set_options',
            'threshold_type' => 'high',
            'required_weight' => $accountInfo['thresholds']['high'],
            'current_weight' => 0,
            'network' => $this->networkName,
            'title' => "Update Thresholds (Low: {$targetLow}, Med: {$targetMed}, High: {$targetHigh}, Master: {$targetMaster})",
        ];
    }

    /**
     * Builds an unsigned multisig payment transaction
     */
    public function buildMultisigPaymentXdr(
        string $sourceAccount,
        string $destination,
        string $assetCode,
        ?string $assetIssuer,
        string $amount,
        ?string $memoText = null
    ): array {
        if (!$this->isValidStellarAddress($sourceAccount)) {
            throw new Exception("Invalid source account address.");
        }
        if (!$this->isValidStellarAddress($destination)) {
            throw new Exception("Invalid destination address.");
        }
        if (!is_numeric($amount) || (float) $amount <= 0) {
            throw new Exception("Payment amount must be greater than 0.");
        }

        $accountInfo = $this->getAccountMultisigInfo($sourceAccount);
        $account = $this->sdk->requestAccount($sourceAccount);

        if (strtoupper($assetCode) === 'XLM' || empty($assetCode)) {
            $asset = Asset::native();
            $assetDisplay = 'XLM';
        } else {
            if (!$assetIssuer || !$this->isValidStellarAddress($assetIssuer)) {
                throw new Exception("Issuer address is required for custom asset {$assetCode}.");
            }
            $asset = strlen($assetCode) <= 4
                ? new AssetTypeCreditAlphaNum4($assetCode, $assetIssuer)
                : new AssetTypeCreditAlphaNum12($assetCode, $assetIssuer);
            $assetDisplay = $assetCode;
        }

        $paymentOp = (new PaymentOperationBuilder($destination, $asset, $amount))
            ->setSourceAccount($sourceAccount)
            ->build();

        $txBuilder = (new TransactionBuilder($account, $this->network))
            ->addOperation($paymentOp);

        if (!empty($memoText)) {
            $txBuilder->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, substr($memoText, 0, 28)));
        }

        $tx = $txBuilder->build();
        $unsignedXdr = $tx->toEnvelopeXdrBase64();

        return [
            'unsigned_xdr' => $unsignedXdr,
            'transaction_hash' => bin2hex($tx->hash($this->network)),
            'operation_type' => 'payment',
            'threshold_type' => 'medium',
            'required_weight' => $accountInfo['thresholds']['med'],
            'current_weight' => 0,
            'network' => $this->networkName,
            'title' => "Payment of {$amount} {$assetDisplay} to " . substr($destination, 0, 4) . '...' . substr($destination, -4),
        ];
    }

    /**
     * Inspects and decodes transaction XDR envelope into human-readable data
     */
    public function parseAndInspectXdr(string $xdr): array
    {
        try {
            $tx = AbstractTransaction::fromEnvelopeBase64XdrString($xdr);
        } catch (\Throwable $e) {
            throw new Exception("Failed to decode transaction XDR: " . $e->getMessage());
        }

        $txHash = bin2hex($tx->hash($this->network));
        $signatures = $tx->getSignatures();

        $operationsData = [];
        $highestThresholdCategory = 'low'; // low, medium, high
        $thresholdHierarchy = ['low' => 1, 'medium' => 2, 'high' => 3];

        $sourceAccount = '';
        $fee = 0;
        $memo = '-';

        if ($tx instanceof Transaction) {
            $sourceAccount = $tx->getSourceAccount()->getAccountId();
            $fee = $tx->getFee();

            $memoObj = $tx->getMemo();
            if ($memoObj) {
                $memo = $memoObj->getValue() ?? '-';
            }

            foreach ($tx->getOperations() as $op) {
                $opType = get_class($op);
                $category = 'medium';
                $details = [];

                if ($op instanceof PaymentOperation) {
                    $category = 'medium';
                    $asset = $op->getAsset();
                    $assetCode = $asset->getType() === 'native' ? 'XLM' : $asset->getCode();
                    $details = [
                        'type' => 'payment',
                        'destination' => $op->getDestination(),
                        'amount' => $op->getAmount(),
                        'asset' => $assetCode,
                    ];
                } elseif ($op instanceof SetOptionsOperation) {
                    $category = 'high';
                    $details = [
                        'type' => 'set_options',
                        'master_key_weight' => $op->getMasterKeyWeight(),
                        'low_threshold' => $op->getLowThreshold(),
                        'medium_threshold' => $op->getMediumThreshold(),
                        'high_threshold' => $op->getHighThreshold(),
                        'home_domain' => $op->getHomeDomain(),
                    ];
                    if ($op->getSigner() !== null) {
                        $details['signer_weight'] = $op->getSignerWeight();
                    }
                } else {
                    $details = [
                        'type' => basename(str_replace('\\', '/', $opType)),
                    ];
                }

                if ($thresholdHierarchy[$category] > $thresholdHierarchy[$highestThresholdCategory]) {
                    $highestThresholdCategory = $category;
                }

                $operationsData[] = [
                    'class' => basename(str_replace('\\', '/', $opType)),
                    'source_account' => $op->getSourceAccount() ?? $sourceAccount,
                    'threshold_category' => $category,
                    'details' => $details,
                ];
            }
        }

        return [
            'transaction_hash' => $txHash,
            'source_account' => $sourceAccount,
            'fee_stroops' => $fee,
            'fee_xlm' => $fee / 10000000,
            'memo' => $memo,
            'required_threshold_category' => $highestThresholdCategory,
            'operations_count' => count($operationsData),
            'operations' => $operationsData,
            'signatures_count' => count($signatures),
        ];
    }

    /**
     * Cryptographically verifies attached signatures against account signers and sums verified weights
     */
    public function verifyTransactionSignatures(string $xdr, ?string $accountId = null): array
    {
        try {
            $tx = AbstractTransaction::fromEnvelopeBase64XdrString($xdr);
        } catch (\Throwable $e) {
            throw new Exception("Invalid transaction XDR: " . $e->getMessage());
        }

        $sourceAccount = $accountId;
        if (!$sourceAccount && $tx instanceof Transaction) {
            $sourceAccount = $tx->getSourceAccount()->getAccountId();
        }
        if (!$sourceAccount) {
            throw new Exception("Could not determine source account for transaction.");
        }

        $accountInfo = $this->getAccountMultisigInfo($sourceAccount);
        $signers = $accountInfo['signers'];
        $thresholds = $accountInfo['thresholds'];

        $inspected = $this->parseAndInspectXdr($xdr);
        $thresholdCategory = $inspected['required_threshold_category'];
        $requiredWeight = $thresholds[$thresholdCategory] ?? $thresholds['medium'];

        $txHashBytes = $tx->hash($this->network);
        $signatures = $tx->getSignatures();

        $verifiedSigners = [];
        $totalVerifiedWeight = 0;
        $usedSignerKeys = [];

        foreach ($signatures as $sig) {
            $hint = $sig->getHint();
            $sigBytes = $sig->getSignature();

            // Match hint against account signers
            foreach ($signers as $signer) {
                $signerKey = $signer['key'];

                if (in_array($signerKey, $usedSignerKeys, true)) {
                    continue; // Do not count duplicate signatures from same key
                }

                try {
                    $keyPair = KeyPair::fromAccountId($signerKey);
                    $expectedHint = $keyPair->getHint();

                    if ($hint === $expectedHint && $keyPair->verifySignature($sigBytes, $txHashBytes)) {
                        $usedSignerKeys[] = $signerKey;
                        $totalVerifiedWeight += $signer['weight'];

                        $verifiedSigners[] = [
                            'public_key' => $signerKey,
                            'short_key' => $signer['short_key'],
                            'weight' => $signer['weight'],
                            'is_master' => $signer['is_master'],
                            'verified' => true,
                            'signed_at' => now()->toIso8601String(),
                        ];
                        break;
                    }
                } catch (\Throwable $e) {
                    // Skip invalid signature verification attempt
                }
            }
        }

        $isReady = $totalVerifiedWeight >= $requiredWeight;

        return [
            'account_id' => $sourceAccount,
            'transaction_hash' => bin2hex($txHashBytes),
            'threshold_category' => $thresholdCategory,
            'required_weight' => $requiredWeight,
            'current_weight' => $totalVerifiedWeight,
            'is_ready' => $isReady,
            'missing_weight' => max(0, $requiredWeight - $totalVerifiedWeight),
            'verified_signers' => $verifiedSigners,
            'total_signatures_in_envelope' => count($signatures),
        ];
    }

    /**
     * Merges signatures from a new XDR into an existing XDR without duplicating signatures
     */
    public function mergeTransactionSignatures(string $existingXdr, string $newXdr): string
    {
        $existingTx = AbstractTransaction::fromEnvelopeBase64XdrString($existingXdr);
        $newTx = AbstractTransaction::fromEnvelopeBase64XdrString($newXdr);

        // Verify transaction hashes match
        $existingHash = $existingTx->hash($this->network);
        $newHash = $newTx->hash($this->network);

        if ($existingHash !== $newHash) {
            throw new Exception("Cannot merge signatures: Transaction contents do not match.");
        }

        $signaturesMap = [];

        foreach ($existingTx->getSignatures() as $sig) {
            $key = bin2hex($sig->getHint()) . '_' . bin2hex($sig->getSignature());
            $signaturesMap[$key] = $sig;
        }

        foreach ($newTx->getSignatures() as $sig) {
            $key = bin2hex($sig->getHint()) . '_' . bin2hex($sig->getSignature());
            if (!isset($signaturesMap[$key])) {
                $signaturesMap[$key] = $sig;
            }
        }

        $mergedSignatures = array_values($signaturesMap);
        $existingTx->setSignatures($mergedSignatures);

        return $existingTx->toEnvelopeXdrBase64();
    }

    /**
     * Submits a fully signed transaction to the Stellar network
     */
    public function submitTransaction(string $signedXdr): array
    {
        try {
            $tx = Transaction::fromEnvelopeBase64XdrString($signedXdr);
            $response = $this->sdk->submitTransaction($tx);

            if ($response->isSuccessful()) {
                return [
                    'status' => 'success',
                    'hash' => $response->getHash(),
                    'ledger' => $response->getLedger(),
                    'message' => 'Transaction submitted and confirmed on the Stellar network!',
                ];
            }

            // Extract Horizon error details
            $extras = $response->getExtras();
            $resultCodes = $extras ? $extras->getResultCodes() : null;
            $txCode = $resultCodes ? $resultCodes->getTransactionResultCode() : 'unknown';
            $opCodes = $resultCodes && $resultCodes->getOperationResultCodes() ? implode(', ', $resultCodes->getOperationResultCodes()) : '';

            $readableError = $this->humanizeStellarError($txCode, $opCodes);

            return [
                'status' => 'error',
                'tx_code' => $txCode,
                'op_codes' => $opCodes,
                'message' => $readableError,
            ];
        } catch (\Throwable $e) {
            Log::error("Stellar transaction submission failure: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Stellar submission error: ' . $e->getMessage(),
            ];
        }
    }

    private function hasSigner(array $signers, string $publicKey): bool
    {
        foreach ($signers as $s) {
            if ($s['key'] === $publicKey) return true;
        }
        return false;
    }

    private function validateSignerWeightChangeSafety(array $accountInfo, string $targetSigner, int $newWeight): void
    {
        $currentSigners = $accountInfo['signers'];
        $thresholds = $accountInfo['thresholds'];

        $futureTotalWeight = 0;
        $found = false;

        foreach ($currentSigners as $s) {
            if ($s['key'] === $targetSigner) {
                $found = true;
                $futureTotalWeight += $newWeight;
            } else {
                $futureTotalWeight += $s['weight'];
            }
        }

        if (!$found) {
            $futureTotalWeight += $newWeight;
        }

        if ($futureTotalWeight < $thresholds['high']) {
            throw new Exception("Removing or reducing this signer would leave the account with only {$futureTotalWeight} total weight, below the High threshold of {$thresholds['high']}. This would lock the account!");
        }
    }

    private function humanizeStellarError(string $txCode, string $opCodes): string
    {
        $map = [
            'tx_bad_auth' => 'Insufficient authorization or invalid signature. The signatures provided do not meet the account threshold.',
            'tx_bad_seq' => 'Invalid sequence number. The account sequence has changed on-chain. Please refresh and regenerate.',
            'tx_insufficient_fee' => 'Network fee too low for current ledger traffic.',
            'tx_insufficient_balance' => 'Source account does not have enough XLM to pay the transaction fee or reserve.',
            'op_underfunded' => 'Insufficient funds for this payment.',
            'op_low_reserve' => 'Adding this signer requires an extra 0.5 XLM reserve balance which this account currently lacks.',
            'op_no_destination' => 'Destination account does not exist. A CreateAccount operation with at least 1 XLM is required.',
            'op_no_trust' => 'Destination account does not have a trustline established for this asset.',
            'op_bad_auth' => 'Signer does not have the required authorization level for this operation.',
        ];

        if (isset($map[$txCode])) {
            return $map[$txCode];
        }

        foreach (explode(', ', $opCodes) as $opCode) {
            if (isset($map[$opCode])) {
                return $map[$opCode];
            }
        }

        return "Stellar network rejected transaction ({$txCode}" . ($opCodes ? ": {$opCodes}" : '') . ").";
    }
}
