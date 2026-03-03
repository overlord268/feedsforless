<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\PackagingType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StorePackagingTypeRequest;
use App\Http\Requests\Api\V1\Admin\UpdatePackagingTypeRequest;
use Illuminate\Http\JsonResponse;

class AdminPackagingTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $packagingTypes = PackagingType::orderBy('name', 'asc')->get();

        return response()->json([
            'data' => $packagingTypes
        ], 200);
    }

    public function store(StorePackagingTypeRequest $request): JsonResponse
    {
        $packagingType = PackagingType::create($request->validated());

        return response()->json([
            'message' => 'Packaging type created successfully',
            'data' => $packagingType
        ], 201);
    }

    public function show(PackagingType $packagingType): JsonResponse
    {
        return response()->json([
            'data' => $packagingType
        ], 200);
    }

    public function update(UpdatePackagingTypeRequest $request, PackagingType $packagingType): JsonResponse
    {
        $packagingType->update($request->validated());

        return response()->json([
            'message' => 'Packaging type updated successfully',
            'data' => $packagingType
        ], 200);
    }

    public function destroy(PackagingType $packagingType): JsonResponse
    {
        $packagingType->delete();

        return response()->json([
            'message' => 'Packaging type deleted successfully'
        ], 200);
    }
}