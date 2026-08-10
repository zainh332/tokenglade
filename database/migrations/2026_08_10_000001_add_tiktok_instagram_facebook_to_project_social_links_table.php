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
        Schema::table('project_social_links', function (Blueprint $table) {
            $table->string('tiktok')->nullable()->after('youtube');
            $table->string('instagram')->nullable()->after('tiktok');
            $table->string('facebook')->nullable()->after('instagram');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_social_links', function (Blueprint $table) {
            $table->dropColumn(['tiktok', 'instagram', 'facebook']);
        });
    }
};
