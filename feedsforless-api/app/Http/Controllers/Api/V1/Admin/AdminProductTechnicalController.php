<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminProductTechnicalController extends Controller
{
    public function syncHandling(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'spec_ids' => ['required', 'array'],
            'spec_ids.*' => ['exists:handling_specs,id']
        ]);

        $product->handlingSpecs()->sync($data['spec_ids']);
        return response()->json(['message' => 'Handling specifications synced successfully']);
    }

    public function syncApplications(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'application_ids' => ['required', 'array'],
            'application_ids.*' => ['exists:typical_applications,id']
        ]);

        $product->typicalApplications()->sync($data['application_ids']);
        return response()->json(['message' => 'Typical applications synced successfully']);
    }
}