<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\HandlingSpec;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreHandlingSpecRequest;
use App\Http\Requests\Api\V1\Admin\UpdateHandlingSpecRequest;
use App\Http\Resources\Api\V1\HandlingSpecResource;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

class AdminHandlingSpecController extends Controller
{
    #[Response(200, 'List of handling specs.', type: 'array{data: list<HandlingSpecResource>}')]
    public function index(): JsonResponse
    {
        return HandlingSpecResource::dataOnlyCollection(
            HandlingSpec::orderBy('label', 'asc')->paginate(50)
        )->toResponse(request());
    }

    public function store(StoreHandlingSpecRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? HandlingSpec::makeUniqueSlug($validated['label']);
        $item = HandlingSpec::create($validated);
        AdminCatalogsController::forgetCache();

        return (new HandlingSpecResource($item))
            ->additional(['message' => 'Handling spec created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(HandlingSpec $handlingSpec): HandlingSpecResource
    {
        return new HandlingSpecResource($handlingSpec);
    }

    public function update(UpdateHandlingSpecRequest $request, HandlingSpec $handlingSpec): JsonResponse
    {
        $validated = $request->validated();

        if (array_key_exists('slug', $validated) && ($validated['slug'] === null || $validated['slug'] === '')) {
            unset($validated['slug']);
        }

        $handlingSpec->update($validated);
        AdminCatalogsController::forgetCache();

        return (new HandlingSpecResource($handlingSpec))
            ->additional(['message' => 'Handling spec updated successfully'])
            ->response();
    }

    public function destroy(HandlingSpec $handlingSpec): JsonResponse
    {
        $handlingSpec->delete();
        AdminCatalogsController::forgetCache();

        return response()->json(['message' => 'Handling spec deleted successfully'], 200);
    }
}
