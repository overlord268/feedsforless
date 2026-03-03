<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('grade')->nullable();
            $table->text('description');
            $table->string('origin_address')->nullable();
            $table->string('stock_status')->default('call');
            $table->string('availability')->nullable();
            $table->date('lead_time')->nullable();
            $table->date('max_lead_time')->nullable();
            $table->string('shelf_life_template')->nullable();
            $table->string('status')->default('draft');
            $table->text('reject_reason')->nullable();
            $table->string('tds_document_path')->nullable();
            $table->string('sds_document_path')->nullable();
            $table->string('coa_document_path')->nullable();
            $table->string('market_trends_link')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};