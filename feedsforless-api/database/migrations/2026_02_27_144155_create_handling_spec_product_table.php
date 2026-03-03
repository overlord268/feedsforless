<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handling_spec_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('handling_spec_id')->constrained()->cascadeOnDelete();
            $table->primary(['product_id', 'handling_spec_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handling_spec_product');
    }
};