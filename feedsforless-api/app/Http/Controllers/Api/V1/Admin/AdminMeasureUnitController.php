<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\MeasureUnit;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMeasureUnitController extends Controller
{
    public function index(): JsonResponse
    {
        $items = MeasureUnit::orderBy('label', 'asc')->paginate(50);
        return response()->json(['data' => $items], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'notation' => ['nullable', 'string', 'max:20'],
        ]);
        $item = MeasureUnit::create($validated);
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Measure unit created successfully', 'data' => $item], 201);
    }

    public function show(MeasureUnit $measureUnit): JsonResponse
    {
        return response()->json(['data' => $measureUnit], 200);
    }

    public function update(Request $request, MeasureUnit $measureUnit): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'notation' => ['nullable', 'string', 'max:20'],
        ]);
        $measureUnit->update($validated);
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Measure unit updated successfully', 'data' => $measureUnit], 200);
    }

    public function destroy(MeasureUnit $measureUnit): JsonResponse
    {
        $measureUnit->delete();
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Measure unit deleted successfully'], 200);
    }
}
