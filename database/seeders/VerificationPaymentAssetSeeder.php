<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VerificationPaymentAsset;

class VerificationPaymentAssetSeeder extends Seeder
{
    public function run(): void
    {
        VerificationPaymentAsset::truncate();

        $VerificationPaymentAssets = [
            [
                'asset_code' => 'XLM',
                'asset_issuer' => null,
                'amount' => 175,
                'position' => 1,
            ],

            [
                'asset_code' => 'USDC',
                'asset_issuer' => 'GA5ZSEJYB37JRC5AVCIA5MOP4RHTM335X2KGX3IHOJAPP5RE34K4KZVN',
                'amount' => 30,
                'position' => 2,
            ],

            [
                'asset_code' => 'TKG',
                'asset_issuer' => 'GAM3PID2IOBTNCBMJXHIAS4EO3GQXAGRX4UB6HTQY2DUOVL3AQRB4UKQ',
                'amount' => 10000,
                'position' => 3,
            ],
        ];

        foreach ($VerificationPaymentAssets as $VerificationPaymentAsset) {
            VerificationPaymentAsset::create($VerificationPaymentAsset);
        }
    }
}
