<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volume_pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_packaging_id')->constrained('product_packaging')->cascadeOnDelete();
            $table->string('tier_name');
            $table->integer('min_quantity');
            $table->integer('max_quantity')->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volume_pricing_tiers');
    }
};