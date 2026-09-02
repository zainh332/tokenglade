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
        Schema::dropIfExists('wallet_metrics');
        Schema::dropIfExists('wallet_asset_snapshots');
        Schema::dropIfExists('wallet_portfolio_snapshots');
        Schema::dropIfExists('wallet_events');
        Schema::dropIfExists('wallet_holdings');
        Schema::dropIfExists('wallet_indexing_states');
        Schema::dropIfExists('tracked_wallets');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tables dropped intentionally for real-time Horizon fetching
    }
};
