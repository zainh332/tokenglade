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
        Schema::table('wallet_holdings', function (Blueprint $table) {
            $table->decimal('limit', 30, 7)->nullable();
            $table->boolean('is_authorized')->nullable();
            $table->boolean('is_authorized_to_maintain_liabilities')->nullable();
            $table->boolean('is_clawback_enabled')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_holdings', function (Blueprint $table) {
            $table->dropColumn([
                'limit',
                'is_authorized',
                'is_authorized_to_maintain_liabilities',
                'is_clawback_enabled'
            ]);
        });
    }
};
