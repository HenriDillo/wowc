<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('custom_name');
            $table->text('description')->nullable();
            $table->json('customization_details');
            $table->string('reference_image_path')->nullable();
            $table->integer('quantity');
            $table->decimal('price_estimate', 10, 2)->nullable();
            $table->enum('status', ['pending_review', 'approved', 'rejected', 'in_production', 'completed'])->default('pending_review');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_orders');
    }
};