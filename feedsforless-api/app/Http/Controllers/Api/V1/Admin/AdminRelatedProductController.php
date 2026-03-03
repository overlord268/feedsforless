<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\RelatedProduct;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminRelatedProductController extends Controller
{
    public function index(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $product->relatedProducts()->with('link')->get()
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'link_id' => ['required', 'exists:products,id', 'different:node_id'],
            'label' => ['required', 'string', 'max:255'],
        ]);

        $data['node_id'] = $product->id;
        $related = RelatedProduct::create($data);

        return response()->json([
            'data' => $related->load('link')
        ], 201);
    }

    public function destroy(Product $product, RelatedProduct $relatedProduct): JsonResponse
    {
        if ($relatedProduct->node_id !== $product->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $relatedProduct->delete();

        return response()->json([], 200);
    }
}