<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_cart_items', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->string('custom_name');
            $table->text('description')->nullable();
            $table->json('customization_details');
            $table->string('reference_image_path')->nullable();
            $table->integer('quantity');
            $table->timestamps();

            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_cart_items');
    }
};