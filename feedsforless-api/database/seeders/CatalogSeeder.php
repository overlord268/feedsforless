<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Product;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $fertilizers = Category::create([
            'label' => 'Fertilizers',
            'slug' => 'fertilizers',
        ]);

        $feedAdditives = Category::create([
            'label' => 'Feed Additives',
            'slug' => 'feed-additives',
        ]);

        $urea = Product::create([
            'sku' => 'UR-46-00-00',
            'name' => 'Urea 46-0-0',
            'slug' => Str::slug('Urea 46-0-0'),
            'grade' => 'Agricultural',
            'description' => 'High quality solid nitrogen fertilizer.',
            'stock_status' => 'in_stock',
            'availability' => 'Immediate',
            'status' => 'published',
        ]);

        $urea->categories()->attach($fertilizers->id);

        $calcium = Product::create([
            'sku' => 'CA-FEED-100',
            'name' => 'Calcium Carbonate',
            'slug' => Str::slug('Calcium Carbonate'),
            'grade' => 'Feed Grade',
            'description' => 'Essential calcium supplement for livestock.',
            'stock_status' => 'call',
            'availability' => 'Lead time 2 weeks',
            'status' => 'published',
        ]);

        $calcium->categories()->attach($feedAdditives->id);
    }
}