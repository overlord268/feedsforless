<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\HandlingSpec;
use App\Domains\Catalog\Models\MeasureUnit;
use App\Domains\Catalog\Models\NutritionalParameter;
use App\Domains\Catalog\Models\PackagingType;
use App\Domains\Catalog\Models\Parameter;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\TestMethod;
use App\Domains\Catalog\Models\TypicalApplication;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AdminCatalogsController extends Controller
{
    private const CACHE_TTL_SECONDS = 300;

    public function index(): JsonResponse
    {
        $data = Cache::remember('admin:catalogs', self::CACHE_TTL_SECONDS, function () {
            return [
                'categories' => Category::orderBy('label')->get(['id', 'label', 'slug', 'parent_id']),
                'packaging_types' => PackagingType::orderBy('name')->get(['id', 'name']),
                'parameters' => Parameter::orderBy('label')->get(['id', 'label', 'type']),
                'nutritional_parameters' => NutritionalParameter::orderBy('label')->get(['id', 'label', 'notation']),
                'test_methods' => TestMethod::orderBy('label')->get(['id', 'label']),
                'measure_units' => MeasureUnit::orderBy('label')->get(['id', 'label', 'notation']),
                'handling_specs' => HandlingSpec::orderBy('label')->get(['id', 'label']),
                'typical_applications' => TypicalApplication::orderBy('label')->get(['id', 'label']),
                'products' => Product::orderBy('created_at', 'desc')->limit(300)->get(['id', 'sku', 'name']),
            ];
        });

        return response()->json(['data' => $data], 200);
    }

    public static function forgetCache(): void
    {
        Cache::forget('admin:catalogs');
    }
}
