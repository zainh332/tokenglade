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
        Schema::create('liquidity_pool_participants', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_address', 64)->unique()->index();
            $table->decimal('pool_shares', 30, 7)->default(0);
            $table->decimal('tkg_amount', 30, 7)->default(0);
            $table->decimal('xlm_amount', 30, 7)->default(0);
            $table->boolean('is_active')->default(false); // active in system (users.status == 1)
            $table->string('wallet_status', 20)->default('active'); // active/inactive on Stellar network
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidity_pool_participants');
    }
};
