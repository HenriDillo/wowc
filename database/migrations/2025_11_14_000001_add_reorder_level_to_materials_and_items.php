<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->integer('reorder_level')->nullable()->after('stock');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->integer('reorder_level')->nullable()->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('reorder_level');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('reorder_level');
        });
    }
};
