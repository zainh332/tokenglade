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
        Schema::create('token_large_events', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 12);
            $table->string('asset_issuer', 56);
            $table->string('transaction_hash', 64)->unique();
            $table->string('wallet_address', 56);
            $table->string('event_type', 16); // BUY, SELL, LP_ADD, LP_REMOVE
            $table->decimal('token_amount', 30, 10);
            $table->decimal('xlm_value', 30, 10);
            $table->decimal('usd_value', 30, 10)->nullable();
            $table->bigInteger('ledger')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index(['asset_code', 'asset_issuer', 'created_at']);
            $table->index('wallet_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_large_events');
    }
};
