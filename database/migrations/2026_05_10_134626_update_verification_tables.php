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
        /*
        -----------------------------------
        UPDATE verification_transactions
        -----------------------------------
        */

        Schema::table('verification_transactions', function (Blueprint $table) {

            /* PAYMENT ASSET REFERENCE */
            $table->unsignedBigInteger('verification_payment_asset_id')->index()->nullable()->after('verified_project_id');

            /* REFUND */
            $table->decimal('refunded_amount', 20, 7)->default(0)->after('amount');
            $table->string('refund_transaction_hash')->nullable()->after('refunded_amount');

            /*
            REFUND STATUS

            0 = no refund
            1 = refund pending
            2 = refunded
            3 = refund failed
            */

            $table->tinyInteger('refund_status')->default(0)->after('status');
        });

        /*
        -----------------------------------
        REMOVE OLD verification_fee COLUMN
        -----------------------------------
        */

        if (
            Schema::hasColumn(
                'verified_projects',
                'verification_fee'
            )
        ) {

            Schema::table(
                'verified_projects',
                function (Blueprint $table) {

                    $table->dropColumn(
                        'verification_fee'
                    );
                }
            );
        }

        /*
        -----------------------------------
        UPDATE verified_projects
        -----------------------------------
        */

        Schema::table('verified_projects', function (Blueprint $table) {

            /* VERIFICATION TYPE

            1 = manual
            2 = automatic
            */

            $table->tinyInteger('verification_type')->default(1)->after('status');

            /* REJECTION TIME */

            $table->timestamp('rejected_at')->nullable()->after('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        -----------------------------------
        verification_transactions
        -----------------------------------
        */

        Schema::table('verification_transactions', function (Blueprint $table) {

            $table->dropColumn([
                'verification_payment_asset_id',
                'refunded_amount',
                'refund_transaction_hash',
                'refund_status',
            ]);
        });

        /*
        -----------------------------------
        verified_projects
        -----------------------------------
        */

        Schema::table('verified_projects', function (Blueprint $table) {

            $table->decimal(
                'verification_fee',
                20,
                7
            )->nullable();

            $table->dropColumn([
                'verification_type',
                'rejected_at',
            ]);
        });
    }
};
