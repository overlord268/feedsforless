<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('related_products', function (Blueprint $table) {
            $table->foreignId('node_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('link_id')->constrained('products')->cascadeOnDelete();
            $table->string('label');
            $table->primary(['node_id', 'link_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('related_products');
    }
};