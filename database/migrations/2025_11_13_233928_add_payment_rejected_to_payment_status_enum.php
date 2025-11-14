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
        // Update payment_status enum values to include 'pending_verification' and 'payment_rejected'
        Schema::table('orders', function (Blueprint $table) {
            $driver = DB::getDriverName();
            
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('unpaid', 'partially_paid', 'paid', 'pending_verification', 'payment_rejected', 'refunded') DEFAULT 'unpaid'");
            }
            // SQLite doesn't support ALTER ENUM, so we'll handle it in the model validation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('unpaid', 'partially_paid', 'paid', 'refunded') DEFAULT 'unpaid'");
            }
        });
    }
};
