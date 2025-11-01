<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Step 1: Add new columns
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('id')->nullable();
            $table->string('last_name')->after('first_name')->nullable();
        });

        // Step 2: Copy data if old column exists
        if (Schema::hasColumn('users', 'name')) {
            DB::statement('UPDATE users SET first_name = name');
        }

        // Step 3: Drop old column
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id')->nullable();
            // Copy first_name back to name field
            DB::statement('UPDATE users SET name = first_name');
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};