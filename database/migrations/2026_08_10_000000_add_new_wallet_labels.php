<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $newLabels = [
            'Smart Contract',
            'Liquidity Pool',
            'AMM Pool',
            'Escrow',
            'Distributor',
            'Fee Collector',
            'Market Maker Reward',
        ];

        foreach ($newLabels as $label) {
            DB::table('wallet_labels')->insertOrIgnore([
                'name'       => $label,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $newLabels = [
            'Smart Contract',
            'Liquidity Pool',
            'AMM Pool',
            'Escrow',
            'Distributor',
            'Fee Collector',
            'Market Maker Reward',
        ];

        DB::table('wallet_labels')->whereIn('name', $newLabels)->delete();
    }
};
