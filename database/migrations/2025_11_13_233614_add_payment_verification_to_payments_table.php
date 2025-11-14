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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('verified_by')->nullable()->after('proof_image')->constrained('users')->nullOnDelete();
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending')->after('verified_by');
            $table->text('verification_notes')->nullable()->after('verification_status');
            $table->timestamp('verified_at')->nullable()->after('verification_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verified_by', 'verification_status', 'verification_notes', 'verified_at']);
        });
    }
};
