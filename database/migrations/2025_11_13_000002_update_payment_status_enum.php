<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update payment_status enum values to include 'partially_paid'
        Schema::table('orders', function (Blueprint $table) {
            // For MySQL compatibility - we'll change the enum to include partial_paid
            // For SQLite, we need to handle this differently
            $driver = DB::getDriverName();
            
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('unpaid', 'partially_paid', 'paid', 'refunded') DEFAULT 'unpaid'");
            }
            // SQLite doesn't support ALTER ENUM, so we'll handle it in the model validation
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid'");
            }
        });
    }
};
