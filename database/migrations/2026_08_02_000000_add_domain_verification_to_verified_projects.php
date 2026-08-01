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
        Schema::table('verified_projects', function (Blueprint $table) {
            $table->string('official_domain')->nullable()->after('website');
            $table->string('claim_id')->nullable()->after('official_domain');
            $table->string('verification_token_hash')->nullable()->after('claim_id');
            $table->string('verification_file_url')->nullable()->after('verification_token_hash');
            $table->string('verification_status')->default('pending_domain_verification')->after('status');
            $table->timestamp('token_expires_at')->nullable()->after('verification_status');
            $table->timestamp('last_check_at')->nullable()->after('token_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verified_projects', function (Blueprint $table) {
            $table->dropColumn([
                'official_domain',
                'claim_id',
                'verification_token_hash',
                'verification_file_url',
                'verification_status',
                'token_expires_at',
                'last_check_at',
            ]);
        });
    }
};
