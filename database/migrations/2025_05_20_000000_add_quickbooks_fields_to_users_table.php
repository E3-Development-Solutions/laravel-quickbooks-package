<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuickBooksFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('qb_access_token')->nullable();
            $table->string('qb_refresh_token')->nullable();
            $table->timestamp('qb_token_expires')->nullable();
            $table->string('qb_realm_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'qb_access_token',
                'qb_refresh_token',
                'qb_token_expires',
                'qb_realm_id',
            ]);
        });
    }
}
