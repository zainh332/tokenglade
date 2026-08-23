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
        // 1. tracked_wallets
        Schema::create('tracked_wallets', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_address', 56)->unique();
            $table->timestamp('first_viewed_at')->nullable();
            $table->timestamp('last_viewed_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamp('last_refreshed_at')->nullable();
            $table->string('tracking_status', 20)->default('ACTIVE'); // ACTIVE, PASSIVE, ARCHIVED
            $table->boolean('is_connected_wallet')->default(false);
            $table->boolean('is_watchlisted')->default(false);
            $table->boolean('is_official_wallet')->default(false);
            $table->timestamps();

            $table->index('tracking_status');
        });

        // 2. wallet_indexing_states
        Schema::create('wallet_indexing_states', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_address', 56)->unique();
            $table->string('last_processed_cursor', 100)->nullable();
            $table->string('last_processed_trade_cursor', 100)->nullable();
            $table->integer('last_processed_ledger')->nullable();
            $table->timestamp('first_indexed_at')->nullable();
            $table->timestamp('last_indexed_at')->nullable();
            $table->string('indexing_status', 20)->default('pending'); // pending, indexing, ready, failed
            $table->boolean('historical_index_complete')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // 3. wallet_holdings
        Schema::create('wallet_holdings', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_address', 56);
            $table->string('asset_type', 30);
            $table->string('asset_code', 56)->default('');
            $table->string('asset_issuer', 56)->default('');
            $table->decimal('balance', 30, 7)->default(0);
            $table->decimal('price_xlm', 30, 10)->nullable();
            $table->decimal('price_usd', 30, 10)->nullable();
            $table->decimal('value_xlm', 30, 10)->nullable();
            $table->decimal('value_usd', 30, 10)->nullable();
            $table->decimal('allocation_percentage', 5, 2)->nullable();
            $table->string('pool_id', 100)->default('');
            $table->timestamps();

            $table->index('wallet_address');
            $table->unique(['wallet_address', 'asset_type', 'asset_code', 'asset_issuer', 'pool_id'], 'wallet_holdings_asset_unique');
        });

        // 4. wallet_events
        Schema::create('wallet_events', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_address', 56);
            $table->string('transaction_hash', 100);
            $table->string('operation_id', 100);
            $table->integer('ledger');
            $table->string('event_type', 40);
            $table->string('asset_code', 56)->nullable();
            $table->string('asset_issuer', 56)->nullable();
            $table->string('counter_asset_code', 56)->nullable();
            $table->string('counter_asset_issuer', 56)->nullable();
            $table->decimal('amount', 30, 7)->nullable();
            $table->decimal('counter_amount', 30, 7)->nullable();
            $table->decimal('value_xlm', 30, 10)->nullable();
            $table->decimal('value_usd', 30, 10)->nullable();
            $table->string('counterparty_address', 56)->nullable();
            $table->timestamp('occurred_at');
            $table->text('metadata_json')->nullable();
            $table->timestamps();

            $table->index('wallet_address');
            $table->index('occurred_at');
            $table->index('event_type');
            $table->unique(['wallet_address', 'operation_id'], 'wallet_events_op_unique');
        });

        // 5. wallet_portfolio_snapshots
        Schema::create('wallet_portfolio_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_address', 56);
            $table->decimal('total_value_xlm', 30, 10);
            $table->decimal('total_value_usd', 30, 10);
            $table->integer('asset_count');
            $table->timestamp('snapshot_at');
            $table->timestamps();

            $table->index('wallet_address');
            $table->index('snapshot_at');
        });

        // 6. wallet_asset_snapshots
        Schema::create('wallet_asset_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_address', 56);
            $table->string('asset_code', 56);
            $table->string('asset_issuer', 56)->nullable();
            $table->decimal('balance', 30, 7);
            $table->decimal('price_xlm', 30, 10)->nullable();
            $table->decimal('price_usd', 30, 10)->nullable();
            $table->decimal('value_xlm', 30, 10)->nullable();
            $table->decimal('value_usd', 30, 10)->nullable();
            $table->timestamp('snapshot_at');
            $table->timestamps();

            $table->index('wallet_address');
            $table->index('snapshot_at');
        });

        // 7. wallet_metrics
        Schema::create('wallet_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_address', 56)->unique();
            $table->decimal('portfolio_value_xlm', 30, 10)->nullable();
            $table->decimal('portfolio_value_usd', 30, 10)->nullable();
            $table->integer('asset_count')->default(0);
            $table->integer('trustline_count')->default(0);
            $table->integer('lp_position_count')->default(0);
            $table->integer('transaction_count_24h')->default(0);
            $table->integer('transaction_count_7d')->default(0);
            $table->integer('transaction_count_30d')->default(0);
            $table->decimal('buy_volume_xlm_24h', 30, 10)->nullable();
            $table->decimal('sell_volume_xlm_24h', 30, 10)->nullable();
            $table->decimal('buy_volume_xlm_7d', 30, 10)->nullable();
            $table->decimal('sell_volume_xlm_7d', 30, 10)->nullable();
            $table->decimal('average_trade_size_xlm', 30, 10)->nullable();
            $table->decimal('largest_trade_xlm', 30, 10)->nullable();
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_metrics');
        Schema::dropIfExists('wallet_asset_snapshots');
        Schema::dropIfExists('wallet_portfolio_snapshots');
        Schema::dropIfExists('wallet_events');
        Schema::dropIfExists('wallet_holdings');
        Schema::dropIfExists('wallet_indexing_states');
        Schema::dropIfExists('tracked_wallets');
    }
};