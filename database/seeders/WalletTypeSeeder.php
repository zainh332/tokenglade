<?php

namespace Database\Seeders;

use App\Models\WalletType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WalletTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        WalletType::truncate();

        $wallets = [
            [
                'name' => 'Frighter',
                'key' => 'frighter',
                'status' => 1,
                'blockchain_id' => 1,
            ],
        ];

        // Insert wallet types into the database
        foreach ($wallets as $wallet) {
            WalletType::create($wallet);
        }

    }
}
