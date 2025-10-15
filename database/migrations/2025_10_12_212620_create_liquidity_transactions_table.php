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
        Schema::create('liquidity_transactions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('liquidity_farming_id')->index()->nullable();
            $table->unsignedBigInteger('liquidity_reward_id')->index()->nullable();

            $table->longText('unsigned_xdr')->nullable();
            $table->longText('signed_xdr')->nullable();
            $table->string('transaction_id')->nullable();

            // mirror naming with staking for drop-in reuse
            $table->boolean('farming_status_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidity_transactions');
    }
};
