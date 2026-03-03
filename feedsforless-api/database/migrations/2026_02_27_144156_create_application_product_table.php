<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('typical_applications')->cascadeOnDelete();
            $table->primary(['product_id', 'application_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_product');
    }
};