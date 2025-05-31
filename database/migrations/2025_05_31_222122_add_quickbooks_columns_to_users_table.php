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
    Schema::table('users', function (Blueprint $table) {
        $table->string('qb_access_token')->nullable();
        $table->string('qb_refresh_token')->nullable();
        $table->timestamp('qb_token_expires_at')->nullable();
        $table->string('qb_realm_id')->nullable();
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn([
            'qb_access_token',
            'qb_refresh_token',
            'qb_token_expires_at',
            'qb_realm_id'
        ]);
    });
}
};
