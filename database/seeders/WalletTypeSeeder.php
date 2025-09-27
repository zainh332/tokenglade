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
                'name' => 'Freighter',
                'key' => 'freighter',
                'status' => 1,
                'blockchain_id' => 1,
            ],
            [
                'name' => 'Rabet',
                'key' => 'rabet',
                'status' => 1,
                'blockchain_id' => 1,
            ],
            [
                'name' => 'Albedo',
                'key' => 'albedo',
                'status' => 1,
                'blockchain_id' => 1,
            ],
            [
                'name' => 'xBull',
                'key' => 'xbull',
                'status' => 1,
                'blockchain_id' => 1,
            ],
        ];

        foreach ($wallets as $wallet) {
            WalletType::create($wallet);
        }

    }
}
