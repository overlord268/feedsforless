<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Parameter;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminParameterController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Parameter::orderBy('label', 'asc')->paginate(50);
        return response()->json(['data' => $items], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
        ]);
        $item = Parameter::create($validated);
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Parameter created successfully', 'data' => $item], 201);
    }

    public function show(Parameter $parameter): JsonResponse
    {
        return response()->json(['data' => $parameter], 200);
    }

    public function update(Request $request, Parameter $parameter): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
        ]);
        $parameter->update($validated);
        return response()->json(['message' => 'Parameter updated successfully', 'data' => $parameter], 200);
    }

    public function destroy(Parameter $parameter): JsonResponse
    {
        $parameter->delete();
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Parameter deleted successfully'], 200);
    }
}
