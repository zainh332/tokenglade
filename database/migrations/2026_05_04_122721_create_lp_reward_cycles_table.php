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
        Schema::create('lp_reward_cycles', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('week_number')->index();
            $table->timestamp('snapshot_date');
            $table->decimal('total_reward_pool', 20, 7);
            $table->decimal('eligible_total_percentage', 10, 6);
            $table->string('memo', 50)->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->index();
            $table->timestamps();

            // Prevent duplicate weekly distributions
            $table->unique('week_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lp_reward_cycles');
    }
};
