<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_transactions', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('verified_project_id')->index();
            $table->string('wallet_address')->index();
            $table->longText('unsigned_xdr')->nullable();
            $table->longText('signed_xdr')->nullable();
            $table->string('transaction_hash')->nullable()->index();
            $table->decimal('amount', 20, 7)->default(0);

            /*
            0 = pending
            1 = submitted
            2 = success
            3 = failed
            */

            $table->tinyInteger('status')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_transactions');
    }
};