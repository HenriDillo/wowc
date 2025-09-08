<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->boolean('visible')->default(true)->after('price');
        });
        Schema::table('items', function (Blueprint $table) {
            $table->boolean('visible')->default(true)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('visible');
        });
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('visible');
        });
    }
};


