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
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AdminCatalogsController extends Controller
{
    private const CACHE_TTL_SECONDS = 300;

    /**
     * Cached bulk snapshot of all catalog masters (used by admin product form).
     */
    #[Response(
        200,
        'All catalog masters in one payload for the admin UI.',
        type: 'array{data: array{categories: list<array{id: int, label: string, slug: string, parent_id: int|null}>, packaging_types: list<array{id: int, name: string, slug: string}>, parameters: list<array{id: int, label: string, slug: string, type: string|null}>, nutritional_parameters: list<array{id: int, label: string, slug: string, notation: string|null}>, test_methods: list<array{id: int, label: string, slug: string}>, measure_units: list<array{id: int, label: string, slug: string, notation: string|null}>, handling_specs: list<array{id: int, label: string, slug: string}>, typical_applications: list<array{id: int, label: string, slug: string}>, products: list<array{id: int, sku: string, name: string}>}}'
    )]
    public function index(): JsonResponse
    {
        $data = Cache::remember('admin:catalogs', self::CACHE_TTL_SECONDS, function () {
            return [
                'categories' => Category::orderBy('label')->get(['id', 'label', 'slug', 'parent_id']),
                'packaging_types' => PackagingType::orderBy('name')->get(['id', 'name', 'slug']),
                'parameters' => Parameter::orderBy('label')->get(['id', 'label', 'slug', 'type']),
                'nutritional_parameters' => NutritionalParameter::orderBy('label')->get(['id', 'label', 'slug', 'notation']),
                'test_methods' => TestMethod::orderBy('label')->get(['id', 'label', 'slug']),
                'measure_units' => MeasureUnit::orderBy('label')->get(['id', 'label', 'slug', 'notation']),
                'handling_specs' => HandlingSpec::orderBy('label')->get(['id', 'label', 'slug']),
                'typical_applications' => TypicalApplication::orderBy('label')->get(['id', 'label', 'slug']),
                'products' => Product::orderBy('created_at', 'desc')->limit(300)->get(['id', 'sku', 'name']),
            ];
        });

        return response()->json(['data' => $data], 200);
    }

    public static function forgetCache(): void
    {
        Cache::forget('admin:catalogs');
        Cache::forget('agent:masters:yaml');
        Cache::forget('agent:masters:yaml:with_products');
    }
}
