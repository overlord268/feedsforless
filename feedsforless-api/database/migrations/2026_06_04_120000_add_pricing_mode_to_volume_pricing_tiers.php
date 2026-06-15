<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('volume_pricing_tiers', function (Blueprint $table) {
            $table->string('pricing_mode', 20)->default('percentage')->after('max_quantity');
            $table->decimal('fixed_price', 12, 2)->nullable()->after('discount_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('volume_pricing_tiers', function (Blueprint $table) {
            $table->dropColumn(['pricing_mode', 'fixed_price']);
        });
    }
};
