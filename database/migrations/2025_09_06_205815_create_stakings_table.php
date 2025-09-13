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
        Schema::create('stakings', function (Blueprint $table) {
            $table->id();
            $table->string('public', 56);
            $table->bigInteger('staking_asset_id');
            $table->decimal('amount', 20, 7);
            $table->tinyInteger('tier')->default(1);
            $table->decimal('apy', 5, 2)->default(0);
            $table->integer('lock_days')->default(0);
            $table->timestamp('unlock_at')->nullable();
            $table->boolean('is_withdrawn')->default(false);
            $table->text('transaction_id')->nullable();
            $table->timestamps();

            $table->index(['public', 'staking_asset_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stakings');
    }
};
