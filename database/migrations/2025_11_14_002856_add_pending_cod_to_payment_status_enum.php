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
        Schema::table('orders', function (Blueprint $table) {
            $driver = DB::getDriverName();
            
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('unpaid', 'partially_paid', 'paid', 'pending_verification', 'payment_rejected', 'pending_cod', 'refunded') DEFAULT 'unpaid'");
            }
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
                DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('unpaid', 'partially_paid', 'paid', 'pending_verification', 'payment_rejected', 'refunded') DEFAULT 'unpaid'");
            }
        });
    }
};
