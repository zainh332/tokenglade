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
        Schema::table('stellar_tokens', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->text('desc')->nullable()->after('name');
            $table->string('website_url')->nullable()->after('desc');
            $table->string('logo')->nullable()->after('website_url');
            $table->tinyInteger('display_decimals')->default(7)->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stellar_tokens', function (Blueprint $table) {
            $table->dropColumn(['name', 'website_url', 'logo', 'display_decimals', 'desc']);
        });
    }
};
