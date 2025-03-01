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
        Schema::create('claim_claimable_balance_ids', function (Blueprint $table) {
            $table->id();
            $table->integer('claim_claimable_balance_id')->index();
            $table->double('token_amount');
            $table->string('balance_id');
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_claimable_balance_ids');
    }
};
