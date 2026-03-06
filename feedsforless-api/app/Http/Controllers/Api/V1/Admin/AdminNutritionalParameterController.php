<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\NutritionalParameter;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNutritionalParameterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min(500, (int) $request->input('per_page', 50)));
        $items = NutritionalParameter::orderBy('label', 'asc')->paginate($perPage);
        return response()->json(['data' => $items], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'notation' => ['nullable', 'string', 'max:50'],
        ]);
        $item = NutritionalParameter::create($validated);
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Nutritional parameter created successfully', 'data' => $item], 201);
    }

    public function show(NutritionalParameter $nutritionalParameter): JsonResponse
    {
        return response()->json(['data' => $nutritionalParameter], 200);
    }

    public function update(Request $request, NutritionalParameter $nutritionalParameter): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'notation' => ['nullable', 'string', 'max:50'],
        ]);
        $nutritionalParameter->update($validated);
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Nutritional parameter updated successfully', 'data' => $nutritionalParameter], 200);
    }

    public function destroy(NutritionalParameter $nutritionalParameter): JsonResponse
    {
        $nutritionalParameter->delete();
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Nutritional parameter deleted successfully'], 200);
    }
}
