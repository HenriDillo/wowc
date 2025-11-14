<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update carrier enum to include 'lbc' and set it as default
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // Update enum to include 'lbc'
            DB::statement("ALTER TABLE orders MODIFY carrier ENUM('lalamove', 'jnt', 'ninjavan', '2go', 'pickup', 'lbc') NULL");
            
            // Update existing NULL carriers to 'lbc'
            DB::table('orders')
                ->whereNull('carrier')
                ->update(['carrier' => 'lbc']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // Revert 'lbc' back to NULL or another value before removing from enum
            DB::table('orders')
                ->where('carrier', 'lbc')
                ->update(['carrier' => null]);
            
            // Remove 'lbc' from enum
            DB::statement("ALTER TABLE orders MODIFY carrier ENUM('lalamove', 'jnt', 'ninjavan', '2go', 'pickup') NULL");
        }
    }
};

