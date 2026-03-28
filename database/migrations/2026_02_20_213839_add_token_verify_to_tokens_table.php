<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tokens', function (Blueprint $table) {
            $table->boolean('token_verify')
                ->default(0)
                ->after('blockchain_id');
        });
    }

    public function down()
    {
        Schema::table('tokens', function (Blueprint $table) {
            $table->dropColumn('token_verify');
        });
    }
};
