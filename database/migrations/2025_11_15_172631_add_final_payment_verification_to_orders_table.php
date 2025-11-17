<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('final_payment_verified')->default(false)->after('remaining_balance');
            $table->timestamp('final_payment_verified_at')->nullable()->after('final_payment_verified');
            $table->foreignId('final_payment_verified_by')->nullable()->after('final_payment_verified_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['final_payment_verified_by']);
            $table->dropColumn(['final_payment_verified', 'final_payment_verified_at', 'final_payment_verified_by']);
        });
    }
};
