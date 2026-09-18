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
        Schema::create('multisig_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('account_id', 56)->index(); // Stellar public address (G...)
            $table->string('title')->nullable(); // Human-readable description
            $table->string('operation_type', 32)->default('custom'); // payment, set_options, change_trust, etc.
            $table->string('threshold_type', 16)->default('medium'); // low, medium, high
            $table->unsignedSmallInteger('required_weight')->default(1);
            $table->unsignedSmallInteger('current_weight')->default(0);
            $table->longText('unsigned_xdr');
            $table->longText('signed_xdr');
            $table->json('signatures_json')->nullable();
            $table->string('transaction_hash', 64)->index();
            $table->string('status', 24)->default('pending')->index(); // pending, ready, submitted, failed, cancelled
            $table->string('stellar_tx_hash', 64)->nullable()->index();
            $table->string('network', 16)->default('public');
            $table->string('created_by', 56)->index();
            $table->text('error_message')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multisig_transactions');
    }
};
