<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\TestMethod;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTestMethodController extends Controller
{
    public function index(): JsonResponse
    {
        $items = TestMethod::orderBy('label', 'asc')->paginate(50);
        return response()->json(['data' => $items], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
        ]);
        $item = TestMethod::create($validated);
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Test method created successfully', 'data' => $item], 201);
    }

    public function show(TestMethod $testMethod): JsonResponse
    {
        return response()->json(['data' => $testMethod], 200);
    }

    public function update(Request $request, TestMethod $testMethod): JsonResponse
    {
        $validated = $request->validate([
            'label' => ['sometimes', 'required', 'string', 'max:255'],
        ]);
        $testMethod->update($validated);
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Test method updated successfully', 'data' => $testMethod], 200);
    }

    public function destroy(TestMethod $testMethod): JsonResponse
    {
        $testMethod->delete();
        AdminCatalogsController::forgetCache();
        return response()->json(['message' => 'Test method deleted successfully'], 200);
    }
}
