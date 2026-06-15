<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /** @var array<string, string> table => label column */
    private array $tables = [
        'packaging_types' => 'name',
        'parameters' => 'label',
        'test_methods' => 'label',
        'measure_units' => 'label',
        'nutritional_parameters' => 'label',
        'handling_specs' => 'label',
        'typical_applications' => 'label',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $labelColumn) {
            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                if (! Schema::hasColumn($table, 'slug')) {
                    $blueprint->string('slug')->nullable()->after('id');
                }
            });
        }

        foreach ($this->tables as $table => $labelColumn) {
            $this->backfillSlugs($table, $labelColumn);
        }

        foreach ($this->tables as $table => $labelColumn) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->unique('slug');
            });
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->tables) as $table) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->dropUnique(['slug']);
                $blueprint->dropColumn('slug');
            });
        }
    }

    private function backfillSlugs(string $table, string $labelColumn): void
    {
        $rows = DB::table($table)->orderBy('id')->get(['id', $labelColumn]);

        foreach ($rows as $row) {
            $base = Str::slug((string) $row->{$labelColumn});
            $slug = $base;
            $suffix = 0;

            while (
                DB::table($table)->where('slug', $slug)->where('id', '!=', $row->id)->exists()
            ) {
                $suffix++;
                $slug = $base.'-'.$suffix;
            }

            DB::table($table)->where('id', $row->id)->update(['slug' => $slug]);
        }
    }
};
