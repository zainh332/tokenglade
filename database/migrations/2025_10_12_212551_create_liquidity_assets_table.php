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
        Schema::create('liquidity_assets', function (Blueprint $table) {
            $table->id();

            $table->enum('network', ['public', 'testnet'])->default('public');

            // Reward asset info (e.g., TKG)
            $table->string('code', 12);
            $table->string('issuer', 56)->nullable();
            $table->enum('type', ['native', 'credit_alphanum4', 'credit_alphanum12'])->default('credit_alphanum4');

            // (Optional) identify the pool pair for reference/UX
            $table->string('pool_asset_a_code', 12)->nullable();
            $table->string('pool_asset_a_issuer', 56)->nullable();
            $table->string('pool_asset_b_code', 12)->nullable();
            $table->string('pool_asset_b_issuer', 56)->nullable();

            $table->string('name')->nullable();
            $table->unsignedTinyInteger('display_decimals')->default(7);
            $table->decimal('min_farm', 20, 7)->default(0);
            $table->boolean('is_active')->default(true);

            $table->string('home_domain')->nullable();
            $table->string('logo_url')->nullable();

            $table->timestamps();

            $table->unique(['network', 'code', 'issuer'], 'uniq_liq_asset_network_code_issuer');
            $table->index(['network', 'code']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidity_assets');
    }
};
