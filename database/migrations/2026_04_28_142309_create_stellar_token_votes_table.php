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
        Schema::create('stellar_token_votes', function (Blueprint $table) {
            $table->id();

            // user who voted
            $table->unsignedBigInteger('user_id')->index();

            // token being voted on
            $table->unsignedBigInteger('stellar_market_token_id')->index();

            $table->enum('vote_type', [
                'trusted',
                'suspicious',
                'scam'
            ]);

            $table->integer('vote_weight')->default(1);

            $table->timestamp('last_changed_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['user_id', 'stellar_market_token_id'],
                'unique_user_token_vote'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stellar_token_votes');
    }
};
