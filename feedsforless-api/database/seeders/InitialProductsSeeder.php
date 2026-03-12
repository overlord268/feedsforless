<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Product;

class InitialProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryName = 'Phosphates';
        $category = Category::firstOrCreate(
            ['slug' => Str::slug($categoryName)],
            ['label' => $categoryName]
        );

        $sdsUrl = 'https://feedproducts.net/files/Dicalcium-Phosphate-18.5-SDS.pdf';
        $tdsUrl = 'https://feedproducts.net/files/Dicalcium-Phosphate-18.5-Data.pdf';
        
        $sdsPath = 'product-documents/sds/Dicalcium-Phosphate-18.5-SDS.pdf';
        $tdsPath = 'product-documents/tds/Dicalcium-Phosphate-18.5-Data.pdf';

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($sdsPath)) {
            $sdsContent = file_get_contents($sdsUrl);
            if ($sdsContent) {
                \Illuminate\Support\Facades\Storage::disk('local')->put($sdsPath, $sdsContent);
            }
        }

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($tdsPath)) {
            $tdsContent = file_get_contents($tdsUrl);
            if ($tdsContent) {
                \Illuminate\Support\Facades\Storage::disk('local')->put($tdsPath, $tdsContent);
            }
        }

        $productName = 'Dicalcium Feed Phosphate';
        $product = Product::updateOrCreate(
            ['name' => $productName],
            [
                'sku' => strtoupper(Str::slug($productName)) . '-185',
                'grade' => '18.50%',
                'sds_document_path' => $sdsPath,
                'tds_document_path' => $tdsPath,
                'description' => 'Dicalcium Feed Phosphate 18.5% is a free-flowing, feed-grade calcium and phosphorus supplement used in livestock, poultry, and companion animal diets to support bone development, growth performance, and metabolic function. This product provides a minimum of 18.5% phosphorus and 20–24% calcium and is suitable for inclusion in complete feeds, premixes, and mineral blends.',
                'status' => 'published',
            ]
        );

        if (!$product->categories()->where('category_id', $category->id)->exists()) {
            $product->categories()->attach($category->id);
        }

        $mono_sdsUrl = 'https://feedproducts.net/files/Monocalcium-22.7-SDS.pdf';
        $mono_tdsUrl = 'https://feedproducts.net/files/Monocalcium-22.7-Data.pdf';
        
        $mono_sdsPath = 'product-documents/sds/Monocalcium-22.7-SDS.pdf';
        $mono_tdsPath = 'product-documents/tds/Monocalcium-22.7-Data.pdf';

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($mono_sdsPath)) {
            $sdsContent = file_get_contents($mono_sdsUrl);
            if ($sdsContent) {
                \Illuminate\Support\Facades\Storage::disk('local')->put($mono_sdsPath, $sdsContent);
            }
        }

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($mono_tdsPath)) {
            $tdsContent = file_get_contents($mono_tdsUrl);
            if ($tdsContent) {
                \Illuminate\Support\Facades\Storage::disk('local')->put($mono_tdsPath, $tdsContent);
            }
        }

        $monoProductName = 'Monocalcium Feed Phosphate';
        $monoProduct = Product::updateOrCreate(
            ['name' => $monoProductName],
            [
                'sku' => strtoupper(Str::slug($monoProductName)) . '-227',
                'grade' => '22.70%',
                'sds_document_path' => $mono_sdsPath,
                'tds_document_path' => $mono_tdsPath,
                'description' => 'Monocalcium Feed Phosphate 22.7% is a high-quality, feed-grade source of readily available phosphorus and calcium for livestock, poultry, and companion animal diets. It supports bone development, growth performance, and efficient nutrient utilization. This product provides a minimum of 22.7% phosphorus and is well suited for use in complete feeds, premixes, and mineral formulations.',
                'status' => 'published',
            ]
        );

        if (!$monoProduct->categories()->where('category_id', $category->id)->exists()) {
            $monoProduct->categories()->attach($category->id);
        }

        $mondi_tdsUrl = 'https://feedproducts.net/files/Monodicalcium-Phosphate-21-Data.pdf';
        
        $mondi_tdsPath = 'product-documents/tds/Monodicalcium-Phosphate-21-Data.pdf';

        $mondi_sdsPath = null;

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($mondi_tdsPath)) {
            $tdsContent = file_get_contents($mondi_tdsUrl);
            if ($tdsContent) {
                \Illuminate\Support\Facades\Storage::disk('local')->put($mondi_tdsPath, $tdsContent);
            }
        }

        $mondiProductName = 'Monodicalcium Feed Phosphate';
        $mondiProduct = Product::updateOrCreate(
            ['name' => $mondiProductName],
            [
                'sku' => strtoupper(Str::slug($mondiProductName)) . '-21',
                'grade' => '21%',
                'sds_document_path' => $mondi_sdsPath,
                'tds_document_path' => $mondi_tdsPath,
                'description' => 'Monodicalcium Feed Phosphate 21% is a free-flowing, feed-grade source of calcium and phosphorus designed for use in livestock, poultry, and companion animal nutrition. It supports healthy bone development, growth performance, and metabolic function. The product provides approximately 21% phosphorus along with a balanced calcium content, making it suitable for inclusion in complete feeds, premixes, and mineral blends.',
                'status' => 'published',
            ]
        );

        if (!$mondiProduct->categories()->where('category_id', $category->id)->exists()) {
            $mondiProduct->categories()->attach($category->id);
        }

        $magCategoryName = 'Magnesium Oxide';
        $magCategory = Category::firstOrCreate(
            ['slug' => Str::slug($magCategoryName)],
            ['label' => $magCategoryName]
        );

        $mag1_sdsUrl = 'https://feedproducts.net/files/Magnesium-Oxide-54-.3-2-SDS.pdf';
        $mag1_tdsUrl = 'https://feedproducts.net/files/Magnesium-Oxide-54-Data.pdf';
        
        $mag1_sdsPath = 'product-documents/sds/Magnesium-Oxide-54-.3-2-SDS.pdf';
        $mag1_tdsPath = 'product-documents/tds/Magnesium-Oxide-54-Data.pdf';

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($mag1_sdsPath)) {
            $sdsContent = @file_get_contents($mag1_sdsUrl);
            if ($sdsContent) {
                \Illuminate\Support\Facades\Storage::disk('local')->put($mag1_sdsPath, $sdsContent);
            }
        }

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($mag1_tdsPath)) {
            $tdsContent = @file_get_contents($mag1_tdsUrl);
            if ($tdsContent) {
                \Illuminate\Support\Facades\Storage::disk('local')->put($mag1_tdsPath, $tdsContent);
            }
        }

        $mag1ProductName = 'Magnesium Oxide 54% (0.3-2.0mm)';
        $mag1Product = Product::updateOrCreate(
            ['name' => $mag1ProductName],
            [
                'sku' => 'MAG-OX-54-0320',
                'grade' => '54% MgO (0.3–2.0 mm)',
                'sds_document_path' => $mag1_sdsPath,
                'tds_document_path' => $mag1_tdsPath,
                'description' => 'Magnesium Oxide 54% is a high-purity, feed-grade source of magnesium used in livestock and poultry nutrition to support metabolic function, enzyme activity, and overall animal health. It provides a minimum of 54% magnesium oxide and is commonly used to help maintain proper magnesium levels, particularly in ruminant diets. This product is suitable for use in complete feeds, premixes, and mineral supplements.',
                'status' => 'published',
            ]
        );

        if (!$mag1Product->categories()->where('category_id', $magCategory->id)->exists()) {
            $mag1Product->categories()->attach($magCategory->id);
        }

        $mag2_sdsUrl = 'https://feedproducts.net/files/Magnesium-Oxide-54-.8-3-SDS.pdf';
        $mag2_sdsPath = 'product-documents/sds/Magnesium-Oxide-54-.8-3-SDS.pdf';

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($mag2_sdsPath)) {
            $sdsContent = @file_get_contents($mag2_sdsUrl);
            if ($sdsContent) {
                \Illuminate\Support\Facades\Storage::disk('local')->put($mag2_sdsPath, $sdsContent);
            }
        }

        $mag2ProductName = 'Magnesium Oxide 54% (0.8-3.0mm)';
        $mag2Product = Product::updateOrCreate(
            ['name' => $mag2ProductName],
            [
                'sku' => 'MAG-OX-54-0830',
                'grade' => '54% MgO (0.8–3.0 mm)',
                'sds_document_path' => $mag2_sdsPath,
                'tds_document_path' => $mag1_tdsPath,
                'description' => 'Magnesium Oxide 54% is a high-purity, feed-grade source of magnesium used in livestock and poultry nutrition to support metabolic function, enzyme activity, and overall animal health. It provides a minimum of 54% magnesium oxide and is suitable for inclusion in complete feeds, premixes, and mineral supplements.',
                'status' => 'published',
            ]
        );

        if (!$mag2Product->categories()->where('category_id', $magCategory->id)->exists()) {
            $mag2Product->categories()->attach($magCategory->id);
        }

        $mag3_sdsUrl = 'https://feedproducts.net/files/Magnesium-Oxide-HR-SDS.pdf';
        $mag3_tdsUrl = 'https://feedproducts.net/files/Magnesium-Oxide-HR-Data.pdf';
        
        $mag3_sdsPath = 'product-documents/sds/Magnesium-Oxide-HR-SDS.pdf';
        $mag3_tdsPath = 'product-documents/tds/Magnesium-Oxide-HR-Data.pdf';

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($mag3_sdsPath)) {
            $sdsContent = @file_get_contents($mag3_sdsUrl);
            if ($sdsContent) {
                \Illuminate\Support\Facades\Storage::disk('local')->put($mag3_sdsPath, $sdsContent);
            }
        }

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($mag3_tdsPath)) {
            $tdsContent = @file_get_contents($mag3_tdsUrl);
            if ($tdsContent) {
                \Illuminate\Support\Facades\Storage::disk('local')->put($mag3_tdsPath, $tdsContent);
            }
        }

        $mag3ProductName = 'Magnesium Oxide HR 95 Block Grade';
        $mag3Product = Product::updateOrCreate(
            ['name' => $mag3ProductName],
            [
                'sku' => 'MAG-OX-HR95',
                'grade' => 'HR 95 Block Grade',
                'sds_document_path' => $mag3_sdsPath,
                'tds_document_path' => $mag3_tdsPath,
                'description' => 'Magnesium Oxide HR (feed grade) is a free-flowing, high-purity mineral supplement composed primarily of magnesium oxide (MgO), serving as an essential source of magnesium in animal nutrition. It appears as a white to off-white powder with excellent handling characteristics. In feed formulations, magnesium oxide supports mineral balance, bone development, metabolic processes, and prevents magnesium deficiency in a range of species. Typical MgO content in feed-grade material is 80–90% MgO by weight with magnesium levels reflective of a vital macromineral.',
                'status' => 'published',
            ]
        );

        if (!$mag3Product->categories()->where('category_id', $magCategory->id)->exists()) {
            $mag3Product->categories()->attach($magCategory->id);
        }

        $ureaCategoryName = 'Prilled Urea';
        $ureaCategory = Category::firstOrCreate(
            ['slug' => Str::slug($ureaCategoryName)],
            ['label' => $ureaCategoryName]
        );

        $urea_sdsUrl = 'https://feedproducts.net/files/Feed-Grade-Urea-SDS.pdf';
        $urea_tdsUrl = 'https://feedproducts.net/files/Feed-Grade-Urea-Data.pdf';
        
        $urea_sdsPath = 'product-documents/sds/Feed-Grade-Urea-SDS.pdf';
        $urea_tdsPath = 'product-documents/tds/Feed-Grade-Urea-Data.pdf';

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($urea_sdsPath)) {
            $sdsContent = @file_get_contents($urea_sdsUrl);
            if ($sdsContent) {
                \Illuminate\Support\Facades\Storage::disk('local')->put($urea_sdsPath, $sdsContent);
            }
        }

        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($urea_tdsPath)) {
            $tdsContent = @file_get_contents($urea_tdsUrl);
            if ($tdsContent) {
                \Illuminate\Support\Facades\Storage::disk('local')->put($urea_tdsPath, $tdsContent);
            }
        }

        $ureaProductName = 'Urea Feed Grade';
        $ureaProduct = Product::updateOrCreate(
            ['name' => $ureaProductName],
            [
                'sku' => 'UREA-FG',
                'grade' => 'Feed Grade',
                'sds_document_path' => $urea_sdsPath,
                'tds_document_path' => $urea_tdsPath,
                'description' => 'Feed Grade Urea is a high-purity, free-flowing non-protein nitrogen (NPN) source used in animal nutrition to support rumen microbial protein synthesis and enhance dietary protein balance. It typically contains approximately 46% nitrogen (N) by weight, which can be converted to an equivalent of crude protein when properly utilized by rumen microorganisms. Feed Grade Urea is formulated specifically for safe inclusion in feed mixes and supplements designed for ruminant animals.',
                'status' => 'published',
            ]
        );

        if (!$ureaProduct->categories()->where('category_id', $ureaCategory->id)->exists()) {
            $ureaProduct->categories()->attach($ureaCategory->id);
        }

        $this->command->info('All initial products loaded successfully!');
    }
}
