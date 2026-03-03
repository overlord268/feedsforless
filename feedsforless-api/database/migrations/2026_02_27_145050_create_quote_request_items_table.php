<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('packaging_type_id')->constrained()->cascadeOnDelete();
            $table->integer('qty');
            $table->decimal('estimated_freight_cost', 10, 2)->nullable();
            $table->decimal('estimated_product_cost', 10, 2)->nullable();
            $table->decimal('line_total_cost', 12, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_request_items');
    }
};