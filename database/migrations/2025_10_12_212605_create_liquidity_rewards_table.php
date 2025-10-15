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
        Schema::create('liquidity_rewards', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('liquidity_farming_id')->index();

            // reward amount in reward asset (e.g., TKG)
            $table->decimal('amount', 20, 7);

            // You’ll likely distribute per day/epoch—optional helper columns:
            $table->unsignedBigInteger('epoch_no')->nullable()->index();
            $table->date('for_date')->nullable()->index();

            $table->text('transaction_id')->nullable(); // claim Tx (on-chain), if/when sent
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidity_rewards');
    }
};
