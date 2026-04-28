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
        Schema::create('stellar_market_tokens', function (Blueprint $table) {
            $table->id();

            $table->string('asset_code')->index();
            $table->string('asset_issuer')->index();

            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->string('website')->nullable();

            $table->boolean('is_verified')->default(false);

            $table->unsignedBigInteger('current_holders')->default(0);
            $table->decimal('current_price_usd', 30, 10)->nullable();
            $table->decimal('current_price_xlm', 30, 10)->nullable();

            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('search_count')->default(0);

            $table->timestamp('last_viewed_at')->nullable();

            $table->timestamps();

            $table->unique([
                'asset_code',
                'asset_issuer'
            ], 'unique_market_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stellar_market_tokens');
    }
};
