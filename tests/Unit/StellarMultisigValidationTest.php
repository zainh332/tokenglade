<?php

namespace Tests\Unit;

use App\Services\StellarMultisigService;
use PHPUnit\Framework\TestCase;
use Soneso\StellarSDK\Crypto\KeyPair;

class StellarMultisigValidationTest extends TestCase
{
    protected StellarMultisigService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StellarMultisigService();
    }

    public function test_validates_stellar_ed25519_addresses(): void
    {
        $validKey = KeyPair::random()->getAccountId();
        $this->assertTrue($this->service->isValidStellarAddress($validKey));

        // Invalid cases
        $this->assertFalse($this->service->isValidStellarAddress(''));
        $this->assertFalse($this->service->isValidStellarAddress('S' . substr($validKey, 1))); // Secret seed
        $this->assertFalse($this->service->isValidStellarAddress('G' . substr($validKey, 2))); // Too short
        $this->assertFalse($this->service->isValidStellarAddress('not_a_valid_stellar_key_at_all'));
    }

    public function test_signature_merge_deduplicates_signatures(): void
    {
        $sourceKeyPair = KeyPair::random();
        $signer1 = KeyPair::random();
        $signer2 = KeyPair::random();
        $destination = KeyPair::random();

        $account = new \Soneso\StellarSDK\Account($sourceKeyPair->getAccountId(), new \phpseclib3\Math\BigInteger(100));
        $network = \Soneso\StellarSDK\Network::testnet();

        $op = (new \Soneso\StellarSDK\PaymentOperationBuilder($destination->getAccountId(), \Soneso\StellarSDK\Asset::native(), '10'))
            ->setSourceAccount($sourceKeyPair->getAccountId())
            ->build();

        $tx = (new \Soneso\StellarSDK\TransactionBuilder($account, $network))
            ->addOperation($op)
            ->build();

        // Signer 1 signs
        $tx1 = clone $tx;
        $tx1->sign($signer1, $network);
        $xdr1 = $tx1->toEnvelopeXdrBase64();

        // Signer 2 signs
        $tx2 = clone $tx;
        $tx2->sign($signer2, $network);
        $xdr2 = $tx2->toEnvelopeXdrBase64();

        // Merge signatures
        $mergedXdr = $this->service->mergeTransactionSignatures($xdr1, $xdr2);

        $mergedTx = \Soneso\StellarSDK\Transaction::fromEnvelopeBase64XdrString($mergedXdr);
        $this->assertCount(2, $mergedTx->getSignatures());

        // Merging again should not duplicate
        $remergedXdr = $this->service->mergeTransactionSignatures($mergedXdr, $xdr1);
        $remergedTx = \Soneso\StellarSDK\Transaction::fromEnvelopeBase64XdrString($remergedXdr);
        $this->assertCount(2, $remergedTx->getSignatures());
    }
}
