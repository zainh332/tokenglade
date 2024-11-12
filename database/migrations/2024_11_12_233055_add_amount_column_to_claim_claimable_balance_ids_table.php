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
        Schema::table('claim_claimable_balance_ids', function (Blueprint $table) {
            $table->decimal('token_amount', 20, 8)->after('claim_claimable_balance_id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claim_claimable_balance_ids', function (Blueprint $table) {
            $table->dropColumn('token_amount');
        });
    }
};
