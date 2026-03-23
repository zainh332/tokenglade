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
        Schema::create('verified_projects', function (Blueprint $table) {
            $table->id();

            // Blockchain reference
            $table->unsignedBigInteger('blockchain_id');

            // Token identity
            $table->string('identifier');
            $table->string('asset_code')->default('');

            // Project info
            $table->string('name')->nullable();
            $table->string('website')->nullable();
            $table->string('twitter')->nullable();
            $table->string('email')->nullable();

            // Owner wallet (who requested verification)
            $table->string('wallet_address')->nullable();

            // Verification status
            $table->tinyInteger('status')->default(0);
            // 0 = pending, 1 = approved, 2 = rejected

            $table->timestamp('verified_at')->nullable();

            // Link to payment (IMPORTANT)
            $table->unsignedBigInteger('payment_id')->nullable();

            // Admin notes
            $table->text('notes')->nullable();

            // Who requested
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['blockchain_id', 'identifier', 'updated_by']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verified_projects');
    }
};
