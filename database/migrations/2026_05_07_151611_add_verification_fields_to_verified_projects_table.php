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
        Schema::table('verified_projects', function (Blueprint $table) {
            $table->decimal('verification_fee', 20, 7)->nullable()->after('wallet_address');
            $table->text('rejection_reason')->nullable()->after('notes');

             /*
            Remove old payment relation
            */
             $table->dropColumn('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verified_projects', function (Blueprint $table) {
            $table->dropColumn('verification_fee');
            $table->dropColumn('rejection_reason');

            /*
            Restore payment_id if rollback happens
            */

            $table->unsignedBigInteger('payment_id')
                ->nullable();
        });
    }
};
