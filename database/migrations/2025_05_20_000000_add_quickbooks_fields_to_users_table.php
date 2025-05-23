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
        // Skip this migration in test environment
        if (app()->environment('testing')) {
            return;
        }
        
        // Check if the users table exists before trying to modify it
        if (!Schema::hasTable('users')) {
            return;
        }
        
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'qb_access_token')) {
                $table->string('qb_access_token')->nullable();
            }
            if (!Schema::hasColumn('users', 'qb_refresh_token')) {
                $table->string('qb_refresh_token')->nullable();
            }
            if (!Schema::hasColumn('users', 'qb_token_expires')) {
                $table->timestamp('qb_token_expires')->nullable();
            }
            if (!Schema::hasColumn('users', 'qb_realm_id')) {
                $table->string('qb_realm_id')->nullable();
            }
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
