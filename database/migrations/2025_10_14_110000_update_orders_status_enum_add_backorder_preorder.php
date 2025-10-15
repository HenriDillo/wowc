<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Assumes MySQL. Adjust as needed for other databases.
        DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('pending','processing','completed','cancelled','backorder','preorder') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending'");
    }
};


