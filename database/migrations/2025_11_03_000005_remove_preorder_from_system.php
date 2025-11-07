<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update order types and statuses to use 'backorder' instead of 'pre_order'
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE items MODIFY COLUMN status ENUM('in_stock', 'out_of_stock', 'back_order') NOT NULL DEFAULT 'in_stock'");
        }

        // Update any existing pre_order items to back_order
        DB::table('items')
            ->whereIn(DB::raw('LOWER(status)'), ['pre_order', 'preorder', 'pre-order'])
            ->update(['status' => 'back_order']);

        // Update orders table order_type enum (MySQL-only raw statements)
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN order_type ENUM('standard', 'backorder', 'custom') NOT NULL DEFAULT 'standard'");
            DB::statement("UPDATE orders SET order_type = 'backorder' WHERE order_type = 'preorder'");
        } else {
            // For non-MySQL drivers, attempt a safe update of values without altering enum types
            DB::table('orders')->where('order_type', 'preorder')->update(['order_type' => 'backorder']);
        }

        // Drop pre-order specific columns if they exist
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'preorder_date')) {
                $table->dropColumn('preorder_date');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'is_preorder')) {
                $table->dropColumn('is_preorder');
            }
        });

        // Drop any pre-order related fields on items
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'is_preorder')) {
                $table->dropColumn('is_preorder');
            }
            if (Schema::hasColumn('items', 'preorder_release_date')) {
                $table->dropColumn('preorder_release_date');
            }
        });
    }

    public function down(): void
    {
        // Add back pre-order as an option but don't try to restore data (MySQL only)
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE items MODIFY COLUMN status ENUM('in_stock', 'out_of_stock', 'back_order', 'pre_order') NOT NULL DEFAULT 'in_stock'");
            DB::statement("ALTER TABLE orders MODIFY COLUMN order_type ENUM('standard', 'backorder', 'preorder', 'custom') NOT NULL DEFAULT 'standard'");
        }

        // Re-add columns but they'll be empty
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'is_preorder')) {
                $table->boolean('is_preorder')->default(false);
            }
        });

        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'preorder_release_date')) {
                $table->timestamp('preorder_release_date')->nullable();
            }
        });
    }
};