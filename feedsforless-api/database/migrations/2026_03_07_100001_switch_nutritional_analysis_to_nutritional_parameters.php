<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nutritional_analysis', function (Blueprint $table) {
            $table->dropForeign(['parameter_id']);
            $table->dropColumn('parameter_id');
        });
        Schema::table('nutritional_analysis', function (Blueprint $table) {
            $table->foreignId('nutritional_parameter_id')->after('product_id')->constrained('nutritional_parameters')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('nutritional_analysis', function (Blueprint $table) {
            $table->dropForeign(['nutritional_parameter_id']);
            $table->dropColumn('nutritional_parameter_id');
        });
        Schema::table('nutritional_analysis', function (Blueprint $table) {
            $table->foreignId('parameter_id')->after('product_id')->constrained('parameters')->cascadeOnDelete();
        });
    }
};
