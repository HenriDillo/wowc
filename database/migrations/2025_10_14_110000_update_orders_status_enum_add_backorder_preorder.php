<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only run the raw ALTER for MySQL. Other DBs (SQLite/Postgres) may not support this raw MODIFY syntax; skip there.
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('pending','processing','completed','cancelled','backorder','preorder') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};


