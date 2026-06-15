<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\PackagingType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StorePackagingTypeRequest;
use App\Http\Requests\Api\V1\Admin\UpdatePackagingTypeRequest;
use App\Http\Resources\Api\V1\PackagingTypeResource;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

class AdminPackagingTypeController extends Controller
{
    #[Response(200, 'List of packaging types.', type: 'array{data: list<PackagingTypeResource>}')]
    public function index(): JsonResponse
    {
        return PackagingTypeResource::dataOnlyCollection(
            PackagingType::orderBy('name', 'asc')->get()
        )->toResponse(request());
    }

    public function store(StorePackagingTypeRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? PackagingType::makeUniqueSlug($validated['name']);
        $packagingType = PackagingType::create($validated);
        AdminCatalogsController::forgetCache();

        return (new PackagingTypeResource($packagingType))
            ->additional(['message' => 'Packaging type created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(PackagingType $packagingType): PackagingTypeResource
    {
        return new PackagingTypeResource($packagingType);
    }

    public function update(UpdatePackagingTypeRequest $request, PackagingType $packagingType): JsonResponse
    {
        $validated = $request->validated();
        if (array_key_exists('slug', $validated) && ($validated['slug'] === null || $validated['slug'] === '')) {
            unset($validated['slug']);
        }
        $packagingType->update($validated);
        AdminCatalogsController::forgetCache();

        return (new PackagingTypeResource($packagingType))
            ->additional(['message' => 'Packaging type updated successfully'])
            ->response();
    }

    public function destroy(PackagingType $packagingType): JsonResponse
    {
        $packagingType->delete();
        AdminCatalogsController::forgetCache();

        return response()->json(['message' => 'Packaging type deleted successfully'], 200);
    }
}
