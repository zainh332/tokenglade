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
        Schema::create('token_whale_events', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 12);
            $table->string('asset_issuer', 56);
            $table->string('transaction_hash', 64)->unique();
            $table->string('wallet_address', 56);
            $table->string('event_type', 30); // BUY, SELL, LP_ADD, LP_REMOVE
            $table->decimal('token_amount', 24, 4);
            $table->decimal('xlm_value', 20, 4);
            $table->unsignedBigInteger('ledger');
            $table->timestamps();

            $table->index(['asset_code', 'asset_issuer', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_whale_events');
    }
};
