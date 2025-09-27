<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staking_assets', function (Blueprint $table) {
            $table->id();

            $table->enum('network', ['public', 'testnet'])->default('public');
            $table->string('code', 12);
            $table->string('issuer', 56)->nullable();
            $table->enum('type', ['native', 'credit_alphanum4', 'credit_alphanum12'])->default('credit_alphanum4');

            $table->string('name')->nullable();
            $table->unsignedTinyInteger('display_decimals')->default(7);
            $table->decimal('min_stake', 20, 7)->default(0);
            $table->boolean('is_active')->default(true);

            $table->string('home_domain')->nullable();
            $table->string('logo_url')->nullable();

            $table->timestamps();

            $table->unique(['network', 'code', 'issuer'], 'uniq_asset_network_code_issuer');

            $table->index(['network', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staking_assets');
    }
};
