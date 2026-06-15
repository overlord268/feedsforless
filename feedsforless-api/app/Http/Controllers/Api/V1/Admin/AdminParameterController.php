<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Parameter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreParameterRequest;
use App\Http\Requests\Api\V1\Admin\UpdateParameterRequest;
use App\Http\Resources\Api\V1\ParameterResource;
use App\Http\Resources\DataOnlyResourceCollection;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

class AdminParameterController extends Controller
{
    #[Response(200, 'List of parameters.', type: 'array{data: list<ParameterResource>}')]
    public function index(): JsonResponse
    {
        return ParameterResource::dataOnlyCollection(
            Parameter::orderBy('label', 'asc')->paginate(50)
        )->toResponse(request());
    }

    public function store(StoreParameterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? Parameter::makeUniqueSlug($validated['label']);
        $item = Parameter::create($validated);
        AdminCatalogsController::forgetCache();

        return (new ParameterResource($item))
            ->additional(['message' => 'Parameter created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Parameter $parameter): ParameterResource
    {
        return new ParameterResource($parameter);
    }

    public function update(UpdateParameterRequest $request, Parameter $parameter): JsonResponse
    {
        $validated = $request->validated();

        if (array_key_exists('slug', $validated) && ($validated['slug'] === null || $validated['slug'] === '')) {
            unset($validated['slug']);
        }

        $parameter->update($validated);
        AdminCatalogsController::forgetCache();

        return (new ParameterResource($parameter))
            ->additional(['message' => 'Parameter updated successfully'])
            ->response();
    }

    public function destroy(Parameter $parameter): JsonResponse
    {
        $parameter->delete();
        AdminCatalogsController::forgetCache();

        return response()->json(['message' => 'Parameter deleted successfully'], 200);
    }
}
