<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'mixed' to order_type enum
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY order_type ENUM('standard', 'backorder', 'preorder', 'custom', 'mixed') NOT NULL");
        }
        // SQLite doesn't support direct enum modifications, handled in model validation
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY order_type ENUM('standard', 'backorder', 'preorder', 'custom') NOT NULL");
        }
    }
};
