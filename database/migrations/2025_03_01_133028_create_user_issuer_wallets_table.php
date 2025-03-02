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
        Schema::create('user_issuer_wallets', function (Blueprint $table) {
            $table->id();
            $table->string('user_wallet_address', 60);
            $table->string('issuer_public_key', 60);
            $table->string('issuer_secret_key', 60);
            $table->double('xlm_amount', 20, 7);
            $table->string('unsigned_transaction');
            $table->longText('signed_transaction')->nullable();
            $table->string('memo', 40)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_issuer_wallets');
    }
};
