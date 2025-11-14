<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add parent_order_id for mixed orders parent-child relationships
            if (!Schema::hasColumn('orders', 'parent_order_id')) {
                $table->foreignId('parent_order_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('orders')
                    ->cascadeOnDelete()
                    ->comment('Reference to parent order for mixed order splitting');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'parent_order_id')) {
                $table->dropForeign(['parent_order_id']);
                $table->dropColumn('parent_order_id');
            }
        });
    }
};
