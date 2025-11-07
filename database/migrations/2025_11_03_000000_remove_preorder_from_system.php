<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Update orders table to remove preorder from status enum
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        if ($driver === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'processing', 'backorder', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
        }
        
        // Drop any preorder-specific columns if they exist
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'preorder_date')) {
                $table->dropColumn('preorder_date');
            }
            if (Schema::hasColumn('orders', 'expected_arrival_date')) {
                $table->dropColumn('expected_arrival_date');
            }
        });

        // Remove preorder flags from items table if they exist
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'is_preorder')) {
                $table->dropColumn('is_preorder');
            }
            if (Schema::hasColumn('items', 'preorder_release_date')) {
                $table->dropColumn('preorder_release_date');
            }
        });
    }

    public function down()
    {
        // Update orders table to add preorder back to status enum
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        if ($driver === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'processing', 'backorder', 'preorder', 'completed', 'cancelled') NOT NULL DEFAULT 'pending'");
        }
        
        // Re-add preorder columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('preorder_date')->nullable();
            $table->timestamp('expected_arrival_date')->nullable();
        });

        // Re-add preorder flags to items table
        Schema::table('items', function (Blueprint $table) {
            $table->boolean('is_preorder')->default(false);
            $table->timestamp('preorder_release_date')->nullable();
        });
    }
};