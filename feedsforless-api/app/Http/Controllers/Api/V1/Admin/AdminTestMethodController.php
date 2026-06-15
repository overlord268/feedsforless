<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\TestMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreTestMethodRequest;
use App\Http\Requests\Api\V1\Admin\UpdateTestMethodRequest;
use App\Http\Resources\Api\V1\TestMethodResource;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

class AdminTestMethodController extends Controller
{
    #[Response(200, 'List of test methods.', type: 'array{data: list<TestMethodResource>}')]
    public function index(): JsonResponse
    {
        return TestMethodResource::dataOnlyCollection(
            TestMethod::orderBy('label', 'asc')->paginate(50)
        )->toResponse(request());
    }

    public function store(StoreTestMethodRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['slug'] = $validated['slug'] ?? TestMethod::makeUniqueSlug($validated['label']);
        $item = TestMethod::create($validated);
        AdminCatalogsController::forgetCache();

        return (new TestMethodResource($item))
            ->additional(['message' => 'Test method created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(TestMethod $testMethod): TestMethodResource
    {
        return new TestMethodResource($testMethod);
    }

    public function update(UpdateTestMethodRequest $request, TestMethod $testMethod): JsonResponse
    {
        $validated = $request->validated();

        if (array_key_exists('slug', $validated) && ($validated['slug'] === null || $validated['slug'] === '')) {
            unset($validated['slug']);
        }

        $testMethod->update($validated);
        AdminCatalogsController::forgetCache();

        return (new TestMethodResource($testMethod))
            ->additional(['message' => 'Test method updated successfully'])
            ->response();
    }

    public function destroy(TestMethod $testMethod): JsonResponse
    {
        $testMethod->delete();
        AdminCatalogsController::forgetCache();

        return response()->json(['message' => 'Test method deleted successfully'], 200);
    }
}
