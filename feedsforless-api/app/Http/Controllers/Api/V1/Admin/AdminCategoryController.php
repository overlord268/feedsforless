<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreCategoryRequest;
use App\Http\Requests\Api\V1\Admin\UpdateCategoryRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    #[Response(200, 'List of categories.', type: 'array{data: list<CategoryResource>}')]
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 200), 1), 500);

        return CategoryResource::dataOnlyCollection(
            Category::orderBy('label', 'asc')->paginate($perPage)
        )->toResponse($request);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());
        AdminCatalogsController::forgetCache();

        return (new CategoryResource($category))
            ->additional(['message' => 'Category created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return (new CategoryResource($category))
            ->additional(['message' => 'Category updated successfully'])
            ->response();
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        AdminCatalogsController::forgetCache();

        return response()->json([
            'message' => 'Category deleted successfully',
        ], 200);
    }
}
