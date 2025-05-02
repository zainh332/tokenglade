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
            $table->decimal('total_supply', 20, 7);
            $table->string('user_wallet_address', 60);
            $table->string('issuerPublicKey', 60)->nullable();
            $table->string('issuerSecretkey', 60)->nullable();
            $table->string('current_stellar_transaction_id')->nullable()->index();
            $table->string('memo', 40)->nullable();
            $table->boolean('lock_status')->default(false);
            $table->tinyInteger('status')->default(0);
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
