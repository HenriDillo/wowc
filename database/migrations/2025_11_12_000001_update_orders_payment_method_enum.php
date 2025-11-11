<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL: Alter the enum to include GCash and Bank Transfer
        if (Schema::connection(null)->getConnection()->getDriverName() === 'mysql') {
            Schema::table('orders', function (Blueprint $table) {
                $table->enum('payment_method', ['COD', 'GCash', 'Card', 'Bank Transfer'])->nullable()->change();
            });
        }
        // SQLite doesn't support direct enum changes, so we skip for SQLite (used in tests)
        // In production MySQL, this will update the enum properly
    }

    public function down(): void
    {
        if (Schema::connection(null)->getConnection()->getDriverName() === 'mysql') {
            Schema::table('orders', function (Blueprint $table) {
                $table->enum('payment_method', ['COD', 'GCash', 'Card'])->nullable()->change();
            });
        }
    }
};
