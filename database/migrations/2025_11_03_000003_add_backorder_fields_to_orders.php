<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'back_order_status')) {
                $table->string('back_order_status')->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'expected_restock_date')) {
                $table->date('expected_restock_date')->nullable()->after('back_order_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'back_order_status')) {
                $table->dropColumn('back_order_status');
            }
            if (Schema::hasColumn('orders', 'expected_restock_date')) {
                $table->dropColumn('expected_restock_date');
            }
        });
    }
};
