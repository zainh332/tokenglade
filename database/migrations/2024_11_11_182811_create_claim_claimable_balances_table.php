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
        Schema::create('claim_claimable_balances', function (Blueprint $table) {
            $table->id();
            $table->string('distributor_wallet_key', 60);
            $table->string('issuer_address', 60);
            $table->string('asset_code', 12);
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_claimable_balances');
    }
};
