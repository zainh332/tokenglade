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
        Schema::create('project_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('verified_project_id')->index();
            $table->string('name');
            $table->text('short_description')->nullable();
            $table->text('full_description')->nullable();
            $table->string('category')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('banner_url')->nullable();
            $table->date('launch_date')->nullable();
            $table->timestamps();
        });

        Schema::create('project_official_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_profile_id')->index();
            $table->string('website')->nullable();
            $table->string('documentation')->nullable();
            $table->string('whitepaper')->nullable();
            $table->string('github')->nullable();
            $table->string('medium')->nullable();
            $table->timestamps();
        });

        Schema::create('project_social_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_profile_id')->index();
            $table->string('twitter')->nullable();
            $table->string('telegram')->nullable();
            $table->string('discord')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('reddit')->nullable();
            $table->string('youtube')->nullable();
            $table->timestamps();
        });

        Schema::create('project_official_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_profile_id')->index();
            $table->string('wallet_address');
            $table->string('label');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_official_wallets');
        Schema::dropIfExists('project_social_links');
        Schema::dropIfExists('project_official_links');
        Schema::dropIfExists('project_profiles');
    }
};
