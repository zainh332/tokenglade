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
        Schema::create('project_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('wallet_labels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Seed default categories
        $categories = [
            'Analytics',
            'Infrastructure',
            'DeFi',
            'DEX',
            'Lending',
            'Wallet',
            'Payments',
            'Bridge',
            'Stablecoin',
            'Launchpad',
            'AI',
            'Gaming',
            'NFT',
            'Oracle',
            'RWA',
            'Social',
            'Identity',
            'DAO',
            'Community',
            'Meme',
            'Utility',
            'Other',
        ];
        foreach ($categories as $cat) {
            DB::table('project_categories')->insertOrIgnore([
                'name'       => $cat,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed default wallet labels
        $labels = [
            'Treasury',
            'Foundation',
            'Team',
            'Founder',
            'Advisor',
            'Marketing',
            'Development',
            'Operations',
            'Reserve',
            'Liquidity',
            'Liquidity Rewards',
            'Staking',
            'Staking Rewards',
            'Ecosystem Fund',
            'Community',
            'Community Rewards',
            'Partnerships',
            'Airdrop',
            'Burn',
            'Vesting',
            'Investor',
            'Exchange',
            'Market Maker',
            'Grants',
            'DAO',
            'Bridge',
            'Multisig',
            'Other',
        ];
        foreach ($labels as $label) {
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
        Schema::dropIfExists('wallet_labels');
        Schema::dropIfExists('project_categories');
    }
};
