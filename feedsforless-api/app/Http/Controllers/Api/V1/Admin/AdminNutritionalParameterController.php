<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\NutritionalParameter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreNutritionalParameterRequest;
use App\Http\Requests\Api\V1\Admin\UpdateNutritionalParameterRequest;
use App\Http\Resources\Api\V1\NutritionalParameterResource;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminNutritionalParameterController extends Controller
{
    #[Response(200, 'List of nutritional parameters.', type: 'array{data: list<NutritionalParameterResource>}')]
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min(500, (int) $request->input('per_page', 50)));

        return NutritionalParameterResource::dataOnlyCollection(
            NutritionalParameter::orderBy('label', 'asc')->paginate($perPage)
        )->toResponse(request());
    }

    public function store(StoreNutritionalParameterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? NutritionalParameter::makeUniqueSlug($validated['label']);
        $item = NutritionalParameter::create($validated);
        AdminCatalogsController::forgetCache();

        return (new NutritionalParameterResource($item))
            ->additional(['message' => 'Nutritional parameter created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(NutritionalParameter $nutritionalParameter): NutritionalParameterResource
    {
        return new NutritionalParameterResource($nutritionalParameter);
    }

    public function update(UpdateNutritionalParameterRequest $request, NutritionalParameter $nutritionalParameter): JsonResponse
    {
        $validated = $request->validated();

        if (array_key_exists('slug', $validated) && ($validated['slug'] === null || $validated['slug'] === '')) {
            unset($validated['slug']);
        }

        $nutritionalParameter->update($validated);
        AdminCatalogsController::forgetCache();

        return (new NutritionalParameterResource($nutritionalParameter))
            ->additional(['message' => 'Nutritional parameter updated successfully'])
            ->response();
    }

    public function destroy(NutritionalParameter $nutritionalParameter): JsonResponse
    {
        $nutritionalParameter->delete();
        AdminCatalogsController::forgetCache();

        return response()->json(['message' => 'Nutritional parameter deleted successfully'], 200);
    }
}
