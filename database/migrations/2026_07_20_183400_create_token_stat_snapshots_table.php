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
        Schema::create('token_stat_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 12);
            $table->string('asset_issuer', 56);
            $table->unsignedBigInteger('holders')->default(0);
            $table->unsignedBigInteger('trustlines')->default(0);
            $table->unsignedInteger('pools_count')->default(0);
            $table->decimal('liquidity_usd', 20, 4)->default(0);
            $table->decimal('market_cap_usd', 20, 4)->default(0);
            $table->decimal('price_usd', 20, 10)->default(0);
            $table->decimal('circulating_supply', 24, 4)->default(0);
            $table->timestamps();

            $table->index(['asset_code', 'asset_issuer', 'created_at'], 'token_snapshot_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_stat_snapshots');
    }
};
