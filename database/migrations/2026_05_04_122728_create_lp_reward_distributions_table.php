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
        Schema::create('lp_reward_distributions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('lp_reward_cycle_id')->index();
            $table->string('wallet_address', 64)->index();
            $table->decimal('pool_share_percentage', 10, 6);
            $table->decimal('reward_amount', 20, 7);
            $table->string('tx_hash', 100)->nullable()->index();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->index();
            $table->timestamps();

            // Prevent duplicate rewards per wallet per cycle
            $table->unique(['lp_reward_cycle_id', 'wallet_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lp_reward_distributions');
    }
};
