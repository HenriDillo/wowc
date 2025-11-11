<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_number')->nullable()->after('payment_status');
            $table->enum('carrier', ['lalamove', 'jnt', 'ninjavan', '2go', 'pickup'])->nullable()->after('tracking_number');
            $table->timestamp('delivered_at')->nullable()->after('carrier');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tracking_number', 'carrier', 'delivered_at']);
        });
    }
};
