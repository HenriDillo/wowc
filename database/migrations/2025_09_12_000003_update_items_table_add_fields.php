<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'category')) {
                $table->string('category')->nullable()->after('name');
            }
            if (!Schema::hasColumn('items', 'description')) {
                $table->text('description')->nullable()->after('category');
            }
            if (!Schema::hasColumn('items', 'visible')) {
                $table->boolean('visible')->default(true)->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'category')) {
                $table->dropColumn('category');
            }
            if (Schema::hasColumn('items', 'description')) {
                $table->dropColumn('description');
            }
            // keep visible in case others rely on it
        });
    }
};


