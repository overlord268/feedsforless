<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug', 255)->nullable()->after('name');
        });

        $used = [];
        foreach (DB::table('products')->orderBy('id')->get() as $p) {
            $base = Str::slug($p->name ?: 'product');
            $slug = $base;
            $n = 0;
            while (isset($used[$slug]) && $used[$slug] !== (int) $p->id) {
                $n++;
                $slug = $base . '-' . $n;
            }
            $used[$slug] = (int) $p->id;
            DB::table('products')->where('id', $p->id)->update(['slug' => $slug]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('slug', 255)->nullable(false)->change();
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
