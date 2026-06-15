<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\TypicalApplication;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreTypicalApplicationRequest;
use App\Http\Requests\Api\V1\Admin\UpdateTypicalApplicationRequest;
use App\Http\Resources\Api\V1\TypicalApplicationResource;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

class AdminTypicalApplicationController extends Controller
{
    #[Response(200, 'List of typical applications.', type: 'array{data: list<TypicalApplicationResource>}')]
    public function index(): JsonResponse
    {
        return TypicalApplicationResource::dataOnlyCollection(
            TypicalApplication::orderBy('label', 'asc')->paginate(50)
        )->toResponse(request());
    }

    public function store(StoreTypicalApplicationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? TypicalApplication::makeUniqueSlug($validated['label']);
        $item = TypicalApplication::create($validated);
        AdminCatalogsController::forgetCache();

        return (new TypicalApplicationResource($item))
            ->additional(['message' => 'Typical application created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(TypicalApplication $typicalApplication): TypicalApplicationResource
    {
        return new TypicalApplicationResource($typicalApplication);
    }

    public function update(UpdateTypicalApplicationRequest $request, TypicalApplication $typicalApplication): JsonResponse
    {
        $validated = $request->validated();

        if (array_key_exists('slug', $validated) && ($validated['slug'] === null || $validated['slug'] === '')) {
            unset($validated['slug']);
        }

        $typicalApplication->update($validated);
        AdminCatalogsController::forgetCache();

        return (new TypicalApplicationResource($typicalApplication))
            ->additional(['message' => 'Typical application updated successfully'])
            ->response();
    }

    public function destroy(TypicalApplication $typicalApplication): JsonResponse
    {
        $typicalApplication->delete();
        AdminCatalogsController::forgetCache();

        return response()->json(['message' => 'Typical application deleted successfully'], 200);
    }
}
