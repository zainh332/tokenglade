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
        Schema::create('stellar_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 12);
            $table->string('total_supply', 12);
            $table->string('distributor_wallet_key', 60);
            $table->string('issuerPublicKey', 60);
            $table->string('issuerSecretkey', 60);
            $table->string('memo', 40)->nullable();
            $table->tinyInteger('lock_status')->nullable();
            $table->tinyInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stellar_tokens');
    }
};
