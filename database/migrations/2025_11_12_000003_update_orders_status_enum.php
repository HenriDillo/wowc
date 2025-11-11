<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL: Update the enum to include new status values
        if (Schema::connection(null)->getConnection()->getDriverName() === 'mysql') {
            Schema::table('orders', function (Blueprint $table) {
                $table->enum('status', [
                    'pending', 
                    'processing', 
                    'backorder', 
                    'ready_to_ship',
                    'ready_for_delivery',
                    'shipped', 
                    'delivered',
                    'in_design',
                    'in_production',
                    'completed', 
                    'cancelled'
                ])->change();
            });
        }
        // SQLite doesn't support direct enum changes, so we skip for SQLite (used in tests)
    }

    public function down(): void
    {
        if (Schema::connection(null)->getConnection()->getDriverName() === 'mysql') {
            Schema::table('orders', function (Blueprint $table) {
                $table->enum('status', ['pending', 'processing', 'backorder', 'completed', 'cancelled'])->change();
            });
        }
    }
};
