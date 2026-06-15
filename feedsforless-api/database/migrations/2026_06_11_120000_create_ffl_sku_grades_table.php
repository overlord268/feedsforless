<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ffl_sku_grade_rules');
        Schema::dropIfExists('ffl_sku_grades');

        Schema::create('ffl_sku_grades', function (Blueprint $table) {
            $table->id();
            $table->string('grade_spec');
            $table->string('sku_code', 40);
            $table->unique('grade_spec');
            $table->index('sku_code');
            $table->timestamps();
        });

        $now = now();
        $grades = [
            ['grade_spec' => '18.5%', 'sku_code' => '185'],
            ['grade_spec' => '22.7%', 'sku_code' => '227'],
            ['grade_spec' => '21.0%', 'sku_code' => '210'],
            ['grade_spec' => '21%', 'sku_code' => '210'],
            ['grade_spec' => '54% MgO (0.3–2.0 mm)', 'sku_code' => '54F'],
            ['grade_spec' => '54% MgO (0.8–3.0 mm)', 'sku_code' => '54C'],
            ['grade_spec' => 'HR 95 Block Grade', 'sku_code' => 'HR95'],
            ['grade_spec' => 'Feed Grade', 'sku_code' => 'FG'],
            ['grade_spec' => 'Standard Grade', 'sku_code' => 'STD'],
            ['grade_spec' => 'Standard', 'sku_code' => 'STD'],
            ['grade_spec' => 'Poultry Grade', 'sku_code' => 'PLT'],
            ['grade_spec' => 'Lo Fluor', 'sku_code' => 'LOF'],
            ['grade_spec' => '70/30 CB', 'sku_code' => '7030'],
        ];

        foreach ($grades as $grade) {
            DB::table('ffl_sku_grades')->insert([
                ...$grade,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Cache::forget('ffl_sku.grades');
    }

    public function down(): void
    {
        Schema::dropIfExists('ffl_sku_grades');
    }
};
