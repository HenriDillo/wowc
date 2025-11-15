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
            $table->unsignedBigInteger('final_payment_verified_by')->nullable()->after('final_payment_verified');
            $table->timestamp('final_payment_verified_at')->nullable()->after('final_payment_verified_by');
            $table->text('final_payment_verification_notes')->nullable()->after('final_payment_verified_at');
            
            $table->foreign('final_payment_verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['final_payment_verified_by']);
            $table->dropColumn(['final_payment_verified', 'final_payment_verified_by', 'final_payment_verified_at', 'final_payment_verification_notes']);
        });
    }
};
