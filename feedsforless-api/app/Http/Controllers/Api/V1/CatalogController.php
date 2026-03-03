<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProductResource;
use App\Http\Resources\Api\V1\ProductDetailResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $products = Product::where('status', 'published')->paginate(15);

        return ProductResource::collection($products);
    }

    public function show(Product $product): ProductDetailResource
    {
        $product->load(['categories', 'packaging']);

        return new ProductDetailResource($product);
    }
}