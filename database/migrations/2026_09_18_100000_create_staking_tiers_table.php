<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('staking_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('tier');
            $table->string('name', 100);
            $table->decimal('min_amount', 20, 7);
            $table->decimal('max_amount', 20, 7)->nullable();
            $table->decimal('apy', 5, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('staking_tiers')->insert([
            [
                'tier' => 1,
                'name' => 'Tier 1',
                'min_amount' => 1500,
                'max_amount' => 9999,
                'apy' => 12.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tier' => 2,
                'name' => 'Tier 2',
                'min_amount' => 10000,
                'max_amount' => 49999,
                'apy' => 15.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tier' => 3,
                'name' => 'Tier 3',
                'min_amount' => 50000,
                'max_amount' => 99999,
                'apy' => 16.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tier' => 4,
                'name' => 'Tier 4',
                'min_amount' => 100000,
                'max_amount' => null,
                'apy' => 18.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staking_tiers');
    }
};
