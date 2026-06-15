<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ffl_sku_grade_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('rule_type', 40);
            $table->string('match_value')->nullable();
            $table->string('output', 40);
            $table->unsignedSmallInteger('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $rules = [
            ['name' => 'Ratio 70/30 CB', 'rule_type' => 'contains', 'match_value' => '70/30', 'output' => '7030', 'priority' => 10],
            ['name' => 'HR 95 Block Grade', 'rule_type' => 'contains', 'match_value' => 'HR 95', 'output' => 'HR95', 'priority' => 20],
            ['name' => 'Feed Grade', 'rule_type' => 'contains', 'match_value' => 'feed grade', 'output' => 'FG', 'priority' => 30],
            ['name' => 'Poultry Grade', 'rule_type' => 'contains', 'match_value' => 'poultry', 'output' => 'PLT', 'priority' => 40],
            ['name' => 'Lo Fluor', 'rule_type' => 'contains', 'match_value' => 'lo fluor', 'output' => 'LOF', 'priority' => 50],
            ['name' => 'Standard Grade', 'rule_type' => 'contains', 'match_value' => 'standard', 'output' => 'STD', 'priority' => 60],
            ['name' => 'Fine granulometry (54F)', 'rule_type' => 'granulometry_fine', 'match_value' => null, 'output' => '{pct}F', 'priority' => 70],
            ['name' => 'Coarse granulometry (54C)', 'rule_type' => 'granulometry_coarse', 'match_value' => null, 'output' => '{pct}C', 'priority' => 80],
            ['name' => 'Percentage', 'rule_type' => 'percentage', 'match_value' => null, 'output' => '{pct10}', 'priority' => 90],
            ['name' => 'Empty / pending', 'rule_type' => 'empty', 'match_value' => null, 'output' => 'PENDING', 'priority' => 100],
        ];

        foreach ($rules as $rule) {
            DB::table('ffl_sku_grade_rules')->insert([
                ...$rule,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ffl_sku_grade_rules');
    }
};
