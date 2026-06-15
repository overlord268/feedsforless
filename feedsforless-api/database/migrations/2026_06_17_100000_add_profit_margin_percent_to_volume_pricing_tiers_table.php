<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('volume_pricing_tiers', function (Blueprint $table) {
            $table->decimal('profit_margin_percent', 5, 2)->nullable()->after('fixed_price');
        });
    }

    public function down(): void
    {
        Schema::table('volume_pricing_tiers', function (Blueprint $table) {
            $table->dropColumn('profit_margin_percent');
        });
    }
};
