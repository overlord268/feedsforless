<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CatalogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::where('status', 'published')->with(['categories', 'packaging.packagingType']);

        if ($request->filled('category_slug')) {
            $query->whereHas('categories', fn ($q) => $q->where('slug', $request->category_slug));
        }

        $products = $query->paginate(15);

        return ProductResource::collection($products);
    }

    /**
     * Single product by ID (for backward compatibility).
     */
    public function show(int $id): ProductResource|JsonResponse
    {
        $model = Product::where('status', 'published')
            ->with(['categories', 'packaging.packagingType', 'typicalApplications'])
            ->find($id);

        if (!$model) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return new ProductResource($model);
    }

    /**
     * Single product by slug (canonical product page).
     */
    public function showBySlug(string $slug): ProductResource|JsonResponse
    {
        $model = Product::where('status', 'published')
            ->where('slug', $slug)
            ->with(['categories', 'packaging.packagingType', 'typicalApplications'])
            ->first();

        if (!$model) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return new ProductResource($model);
    }

    /**
     * Public document preview/download for catalog (TDS, SDS, COA/ODA).
     */
    public function document(Product $product, string $type): BinaryFileResponse|JsonResponse
    {
        if (!in_array($type, ['tds', 'sds', 'coa'], true)) {
            return response()->json(['message' => 'Invalid document type'], 400);
        }
        $pathKey = $type . '_document_path';
        $path = $product->$pathKey;
        if (!$path || !Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'Document not found'], 404);
        }
        $fullPath = Storage::disk('local')->path($path);
        return response()->file($fullPath, [
            'Content-Type' => Storage::disk('local')->mimeType($path) ?: 'application/octet-stream',
        ]);
    }
}