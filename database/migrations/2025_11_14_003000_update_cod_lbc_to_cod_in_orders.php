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
        // Update existing orders with "COD (LBC)" to "COD"
        DB::table('orders')
            ->where('payment_method', 'COD (LBC)')
            ->update(['payment_method' => 'COD']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to "COD (LBC)" if needed
        DB::table('orders')
            ->where('payment_method', 'COD')
            ->update(['payment_method' => 'COD (LBC)']);
    }
};

