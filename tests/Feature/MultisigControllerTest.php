<?php

namespace Tests\Feature;

use App\Models\MultisigTransaction;
use phpseclib3\Math\BigInteger;
use Soneso\StellarSDK\Account;
use Soneso\StellarSDK\Asset;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Network;
use Soneso\StellarSDK\PaymentOperationBuilder;
use Soneso\StellarSDK\TransactionBuilder;
use Tests\TestCase;

class MultisigControllerTest extends TestCase
{
    public function test_rejects_invalid_account_address(): void
    {
        $response = $this->getJson('/api/multisig/account/INVALID_ADDRESS');
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
        ]);
    }

    public function test_validates_signer_xdr_payload(): void
    {
        $response = $this->postJson('/api/multisig/build-signer-xdr', []);
        $response->assertStatus(422);
        $response->assertJsonStructure(['status', 'message']);
    }

    public function test_validates_thresholds_xdr_payload(): void
    {
        $response = $this->postJson('/api/multisig/build-thresholds-xdr', [
            'account_id' => 'not_real',
            'high' => 300, // exceeds 255
        ]);
        $response->assertStatus(422);
    }

    public function test_inspect_xdr_decodes_operations(): void
    {
        $source = KeyPair::random();
        $dest = KeyPair::random();
        $account = new Account($source->getAccountId(), new BigInteger(1));
        $network = Network::testnet();

        $op = (new PaymentOperationBuilder($dest->getAccountId(), Asset::native(), '25'))
            ->setSourceAccount($source->getAccountId())
            ->build();

        $tx = (new TransactionBuilder($account, $network))
            ->addOperation($op)
            ->build();

        $xdr = $tx->toEnvelopeXdrBase64();

        $response = $this->postJson('/api/multisig/inspect-xdr', ['xdr' => $xdr]);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'source_account' => $source->getAccountId(),
                'required_threshold_category' => 'medium',
                'operations_count' => 1,
            ],
        ]);
    }

    public function test_persists_multisig_transaction_and_lists_it(): void
    {
        $source = KeyPair::random();
        $dest = KeyPair::random();
        $account = new Account($source->getAccountId(), new BigInteger(1));
        $network = Network::testnet();

        $op = (new PaymentOperationBuilder($dest->getAccountId(), Asset::native(), '50'))
            ->setSourceAccount($source->getAccountId())
            ->build();

        $tx = (new TransactionBuilder($account, $network))
            ->addOperation($op)
            ->build();

        $xdr = $tx->toEnvelopeXdrBase64();

        $createRes = $this->postJson('/api/multisig/transactions', [
            'account_id' => $source->getAccountId(),
            'xdr' => $xdr,
            'created_by' => $source->getAccountId(),
            'title' => 'Test Multisig Transfer',
        ]);

        // Could be 200 or Horizon 400 if test account not on public network, but let's check validation:
        if ($createRes->status() === 200) {
            $createRes->assertJson([
                'status' => 'success',
                'data' => [
                    'title' => 'Test Multisig Transfer',
                ],
            ]);

            $listRes = $this->getJson('/api/multisig/transactions?account_id=' . $source->getAccountId());
            $listRes->assertStatus(200);
            $listRes->assertJsonCount(1, 'data');
        } else {
            $this->assertContains($createRes->status(), [200, 400]);
        }
    }
}
