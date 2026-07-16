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
        Schema::create('stellar_ohlc_data', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code');
            $table->string('asset_issuer');
            $table->string('timeframe'); // '4h', '1d', '1w'
            $table->unsignedBigInteger('timestamp'); // epoch seconds
            $table->decimal('open', 20, 10);
            $table->decimal('high', 20, 10);
            $table->decimal('low', 20, 10);
            $table->decimal('close', 20, 10);
            $table->decimal('volume', 20, 4);
            $table->timestamps();

            $table->unique(['asset_code', 'asset_issuer', 'timeframe', 'timestamp'], 'ohlc_unique_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stellar_ohlc_data');
    }
};
