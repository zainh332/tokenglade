<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations.
     */
    public function up(): void
    {
        Schema::create('verification_payment_assets', function (Blueprint $table) {

            $table->id();

            /* TOKEN INFO */
            $table->string('asset_code');
            $table->string('asset_issuer')->nullable();

            /* PAYMENT SETTINGS */
            $table->decimal('amount', 20, 7);

            /* STATUS */
            $table->boolean('is_active')->default(true);

            /* SORT ORDER */
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_payment_assets');
    }
};
