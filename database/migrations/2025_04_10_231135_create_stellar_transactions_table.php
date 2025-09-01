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
        Schema::create('stellar_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stellar_token_id')->index()->nullable();
            $table->unsignedBigInteger('claimable_balance_id')->index()->nullable();
            $table->unsignedBigInteger('reclaimable_balance_id')->index()->nullable();

            $table->string('user_wallet_address')->nullable();
            $table->string('transaction_type_id')->index();
            $table->longText('unsigned_xdr')->nullable();
            $table->longText('signed_xdr')->nullable();
            $table->string('tx_hash')->nullable();
            $table->boolean('status')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stellar_transactions');
    }
};
