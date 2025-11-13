<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add columns for partial payment tracking
            if (!Schema::hasColumn('orders', 'required_payment_amount')) {
                $table->decimal('required_payment_amount', 10, 2)->nullable()->default(null)->comment('Amount customer must pay at checkout');
            }
            if (!Schema::hasColumn('orders', 'remaining_balance')) {
                $table->decimal('remaining_balance', 10, 2)->nullable()->default(null)->comment('Remaining balance for partial payments');
            }
            // Update payment_status enum to include 'partially_paid'
            $table->comment = 'Orders table with payment tracking for standard, backorder, and custom orders';
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['required_payment_amount', 'remaining_balance']);
        });
    }
};
