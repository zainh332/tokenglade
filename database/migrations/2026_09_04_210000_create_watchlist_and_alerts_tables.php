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
        // 1. Watchlist Items
        Schema::create('watchlist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('wallet_address')->index();
            $table->string('asset_issuer');
            $table->string('asset_code');
            $table->timestamps();

            $table->unique(['wallet_address', 'asset_issuer', 'asset_code'], 'watchlist_unique_item');
        });

        // 2. Price Alerts
        Schema::create('price_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('wallet_address')->index();
            $table->string('asset_issuer');
            $table->string('asset_code');
            $table->string('condition_type'); // 'price_above', 'price_below', 'pct_change_up', 'pct_change_down'
            $table->decimal('condition_value', 20, 8);
            $table->string('currency', 10)->default('xlm'); // 'xlm' or 'usd'
            $table->json('channels'); // ['push', 'onsite']
            $table->string('status')->default('active')->index(); // 'active', 'fired', 'disabled'
            $table->decimal('initial_price_xlm', 20, 8)->nullable();
            $table->decimal('initial_price_usd', 20, 8)->nullable();
            $table->timestamp('fired_at')->nullable();
            $table->timestamps();
        });

        // 3. Push Subscriptions
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('wallet_address')->index();
            $table->text('endpoint');
            $table->text('keys_p256dh');
            $table->text('keys_auth');
            $table->timestamps();
        });

        // 4. Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('wallet_address')->index();
            $table->uuid('alert_id')->nullable()->index();
            $table->string('asset_code')->nullable();
            $table->string('asset_issuer')->nullable();
            $table->string('title')->nullable();
            $table->text('message');
            $table->boolean('read')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('price_alerts');
        Schema::dropIfExists('watchlist_items');
    }
};
