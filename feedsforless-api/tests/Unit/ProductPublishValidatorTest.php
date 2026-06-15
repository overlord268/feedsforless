<?php

namespace Tests\Unit;

use App\Domains\Catalog\Models\Product;
use App\Services\Catalog\ProductPublishValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductPublishValidatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_product_missing_required_public_fields(): void
    {
        $product = Product::create([
            'name' => 'Test product',
            'slug' => 'test-product',
            'status' => 'draft',
            'stock_status' => 'in_stock',
            'description' => '',
        ]);

        $validator = new ProductPublishValidator;

        try {
            $validator->assertPublishable($product);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('category_ids', $e->errors());
            $this->assertArrayHasKey('packaging', $e->errors());
        }
    }
}
