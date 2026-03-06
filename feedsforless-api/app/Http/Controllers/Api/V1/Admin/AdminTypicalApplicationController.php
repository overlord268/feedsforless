<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\TypicalApplication;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTypicalApplicationController extends Controller
{
    public function index(): JsonResponse
    {
        $items = TypicalApplication::orderBy('label', 'asc')->paginate(50);
        return response()->json(['data' => $items], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $item = TypicalApplication::create($validated);
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Typical application created successfully', 'data' => $item], 201);
    }

    public function show(TypicalApplication $typicalApplication): JsonResponse
    {
        return response()->json(['data' => $typicalApplication], 200);
    }

    public function update(Request $request, TypicalApplication $typicalApplication): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $typicalApplication->update($validated);
        return response()->json(['message' => 'Typical application updated successfully', 'data' => $typicalApplication], 200);
    }

    public function destroy(TypicalApplication $typicalApplication): JsonResponse
    {
        $typicalApplication->delete();
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Typical application deleted successfully'], 200);
    }
}
