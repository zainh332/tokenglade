<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('liquidity_farmings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('liquidity_pool_id');   // maps to a tracked pool (see note below)

            // In LP, users hold pool shares instead of a single-asset amount.
            $table->decimal('lp_shares', 30, 7)->default(0);

            // (Optional but useful) cache the deposit legs for analytics/UX
            $table->decimal('amount_asset_a', 20, 7)->default(0);
            $table->decimal('amount_asset_b', 20, 7)->default(0);

            // Keep parallel fields for easier reuse of UI/services (you can ignore tiers in code)
            $table->tinyInteger('tier')->default(0);                 // not used logically, but kept for structure parity
            $table->decimal('apy', 7, 3)->default(0);                // store last computed/estimated APY for display
            $table->integer('lock_days')->default(0);                // if you decide to lock LP farming positions
            $table->timestamp('unlock_at')->nullable();

            $table->boolean('is_withdrawn')->default(false);
            $table->text('transaction_id')->nullable();

            $table->integer('farming_status_id'); // mirrors staking_status_id style
            $table->timestamps();

            $table->index(['user_id', 'liquidity_pool_id', 'farming_status_id']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidity_farmings');
    }
};
