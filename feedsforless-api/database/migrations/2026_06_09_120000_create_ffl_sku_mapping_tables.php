<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ffl_sku_category_maps', function (Blueprint $table) {
            $table->id();
            $table->string('label')->unique();
            $table->string('code', 10);
            $table->index('code');
            $table->timestamps();
        });

        Schema::create('ffl_sku_product_maps', function (Blueprint $table) {
            $table->id();
            $table->string('product_name')->unique();
            $table->string('code', 10);
            $table->unique('code');
            $table->timestamps();
        });

        Schema::create('ffl_sku_map_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('map_type', 20);
            $table->string('action', 20);
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        $now = now();

        foreach (config('ffl_sku.category_map', []) as $label => $code) {
            DB::table('ffl_sku_category_maps')->insert([
                'label' => $label,
                'code' => $code,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (config('ffl_sku.product_map', []) as $productName => $code) {
            DB::table('ffl_sku_product_maps')->insert([
                'product_name' => $productName,
                'code' => $code,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ffl_sku_map_audits');
        Schema::dropIfExists('ffl_sku_product_maps');
        Schema::dropIfExists('ffl_sku_category_maps');
    }
};
