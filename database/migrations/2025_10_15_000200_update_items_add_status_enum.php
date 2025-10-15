<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'status')) {
                $table->enum('status', ['in_stock', 'out_of_stock', 'pre_order', 'back_order'])->default('in_stock')->after('stock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};


