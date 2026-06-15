<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\MeasureUnit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreMeasureUnitRequest;
use App\Http\Requests\Api\V1\Admin\UpdateMeasureUnitRequest;
use App\Http\Resources\Api\V1\MeasureUnitResource;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

class AdminMeasureUnitController extends Controller
{
    #[Response(200, 'List of measure units.', type: 'array{data: list<MeasureUnitResource>}')]
    public function index(): JsonResponse
    {
        return MeasureUnitResource::dataOnlyCollection(
            MeasureUnit::orderBy('label', 'asc')->paginate(50)
        )->toResponse(request());
    }

    public function store(StoreMeasureUnitRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? MeasureUnit::makeUniqueSlug($validated['label']);
        $item = MeasureUnit::create($validated);
        AdminCatalogsController::forgetCache();

        return (new MeasureUnitResource($item))
            ->additional(['message' => 'Measure unit created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(MeasureUnit $measureUnit): MeasureUnitResource
    {
        return new MeasureUnitResource($measureUnit);
    }

    public function update(UpdateMeasureUnitRequest $request, MeasureUnit $measureUnit): JsonResponse
    {
        $validated = $request->validated();

        if (array_key_exists('slug', $validated) && ($validated['slug'] === null || $validated['slug'] === '')) {
            unset($validated['slug']);
        }

        $measureUnit->update($validated);
        AdminCatalogsController::forgetCache();

        return (new MeasureUnitResource($measureUnit))
            ->additional(['message' => 'Measure unit updated successfully'])
            ->response();
    }

    public function destroy(MeasureUnit $measureUnit): JsonResponse
    {
        $measureUnit->delete();
        AdminCatalogsController::forgetCache();

        return response()->json(['message' => 'Measure unit deleted successfully'], 200);
    }
}
