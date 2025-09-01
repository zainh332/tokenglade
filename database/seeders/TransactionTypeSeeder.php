<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TransactionType::truncate();

       $TransactionTypes = [
            ['name' => 'Token Creation Fee'],
            ['name' => 'Generate Issuer Wallet'],
            ['name' => 'Issuer Wallet Distributor Wallet Trustline'],
            ['name' => 'Created Token Transfer'],
        ];

        // Insert Transaction types into the database
        foreach ($TransactionTypes as $TransactionType) {
            TransactionType::create($TransactionType);
        }
    }
}
