<?php

namespace Database\Seeders;

use App\Models\StakingAsset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;


class StakingAssetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StakingAsset::truncate();
        
        $tkgPublic   = env('TKG_ISSUER_PUBLIC');
        $tkgTestnet  = env('TKG_ISSUER_TESTNET');
        $usdcPublic  = env('USDC_ISSUER_PUBLIC'); 
        $usdcTestnet = env('USDC_ISSUER_TESTNET');

        $rows = [
            // ===== TKG (credit_alphanum4) =====
            [
                'network'           => 'public',
                'code'              => 'TKG',
                'issuer'            => $tkgPublic,
                'type'              => 'credit_alphanum4',
                'name'              => 'TKG',
                'display_decimals'  => 7,
                'min_stake'         => 1500,
                'is_active'         => true,
                'home_domain'       => null,
                'logo_url'          => null,
            ],
            [
                'network'           => 'testnet',
                'code'              => 'TKG',
                'issuer'            => $tkgTestnet,
                'type'              => 'credit_alphanum4',
                'name'              => 'TKG (Testnet)',
                'display_decimals'  => 7,
                'min_stake'         => 1500,
                'is_active'         => true,
                'home_domain'       => null,
                'logo_url'          => null,
            ],

            // ===== XLM (native) =====
            [
                'network'           => 'public',
                'code'              => 'XLM',
                'issuer'            => null,
                'type'              => 'native',
                'name'              => 'Lumens',
                'display_decimals'  => 7,
                'min_stake'         => 10,
                'is_active'         => false,
                'home_domain'       => 'stellar.org',
                'logo_url'          => null,
            ],
            [
                'network'           => 'testnet',
                'code'              => 'XLM',
                'issuer'            => null,
                'type'              => 'native',
                'name'              => 'Lumens (Testnet)',
                'display_decimals'  => 7,
                'min_stake'         => 10,
                'is_active'         => false,
                'home_domain'       => 'stellar.org',
                'logo_url'          => null,
            ],

            // ===== USDC (credit_alphanum12) 
            [
                'network'           => 'public',
                'code'              => 'USDC',
                'issuer'            => $usdcPublic, 
                'type'              => 'credit_alphanum12',
                'name'              => 'USD Coin',
                'display_decimals'  => 7,
                'min_stake'         => 50,
                'is_active'         => false,
                'home_domain'       => null,
                'logo_url'          => null,
            ],
            [
                'network'           => 'testnet',
                'code'              => 'USDC',
                'issuer'            => $usdcTestnet,  
                'type'              => 'credit_alphanum12',
                'name'              => 'USD Coin (Testnet)',
                'display_decimals'  => 7,
                'min_stake'         => 50,
                'is_active'         => false,
                'home_domain'       => null,
                'logo_url'          => null,
            ],
        ];

        // Save or update rows. Skip non-native rows if issuer is missing.
        foreach ($rows as $row) {
            $isNative = $row['type'] === 'native';
            $issuer   = $row['issuer'] ?? null;

            if (!$isNative && empty($issuer)) {
                // Skip non-native assets without an issuer configured
                continue;
            }

            // Normalize empty issuer to null for the unique index
            if ($issuer === '') {
                $row['issuer'] = null;
            }

            StakingAsset::updateOrCreate(
                [
                    'network' => $row['network'],
                    'code'    => $row['code'],
                    'issuer'  => $row['issuer'],
                ],
                Arr::except($row, ['network', 'code', 'issuer'])
            );
        }
    }
}
