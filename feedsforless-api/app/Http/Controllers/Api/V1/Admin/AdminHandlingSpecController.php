<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\HandlingSpec;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminHandlingSpecController extends Controller
{
    public function index(): JsonResponse
    {
        $items = HandlingSpec::orderBy('label', 'asc')->paginate(50);
        return response()->json(['data' => $items], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'requirement' => ['nullable', 'string'],
        ]);
        $item = HandlingSpec::create($validated);
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Handling spec created successfully', 'data' => $item], 201);
    }

    public function show(HandlingSpec $handlingSpec): JsonResponse
    {
        return response()->json(['data' => $handlingSpec], 200);
    }

    public function update(Request $request, HandlingSpec $handlingSpec): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'requirement' => ['nullable', 'string'],
        ]);
        $handlingSpec->update($validated);
        return response()->json(['message' => 'Handling spec updated successfully', 'data' => $handlingSpec], 200);
    }

    public function destroy(HandlingSpec $handlingSpec): JsonResponse
    {
        $handlingSpec->delete();
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Handling spec deleted successfully'], 200);
    }
}
