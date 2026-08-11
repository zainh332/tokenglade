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
        Schema::create('project_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code', 56)->nullable();
            $table->string('asset_issuer', 56)->nullable();
            $table->string('name', 100)->nullable();
            $table->string('email', 150);
            $table->string('topic', 100);
            $table->text('message');
            $table->string('status', 20)->default('pending'); // pending, resolved, ignored
            $table->text('reply')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_inquiries');
    }
};
