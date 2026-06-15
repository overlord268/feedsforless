<?php

namespace App\Services\Catalog;

use App\Domains\Catalog\Models\Product;
use Illuminate\Validation\ValidationException;

class ProductPublishValidator
{
    /**
     * @return array<string, list<string>>
     */
    public function errors(Product $product): array
    {
        $errors = [];

        if ($product->name === null || trim($product->name) === '') {
            $errors['name'] = ['Name is required to publish a product.'];
        }

        if ($product->slug === null || trim($product->slug) === '') {
            $errors['slug'] = ['Slug is required to publish a product.'];
        }

        if ($product->categories()->count() === 0) {
            $errors['category_ids'] = ['At least one category is required to publish a product.'];
        }

        $packaging = $product->packaging()->get();
        if ($packaging->isEmpty()) {
            $errors['packaging'] = ['At least one packaging option is required to publish a product.'];
        } else {
            foreach ($packaging as $index => $option) {
                if ($option->packaging_type_id === null) {
                    $errors["packaging.{$index}.packaging_type_id"] = ['Packaging type is required.'];
                }
                if ($option->quantity_per_pallet === null || $option->quantity_per_pallet < 1) {
                    $errors["packaging.{$index}.quantity_per_pallet"] = ['Quantity per pallet is required.'];
                }
                if ($option->base_price_per_unit === null) {
                    $errors["packaging.{$index}.base_price_per_unit"] = ['Base price per unit is required.'];
                }
            }
        }

        return $errors;
    }

    /**
     * @throws ValidationException
     */
    public function assertPublishable(Product $product): void
    {
        $errors = $this->errors($product);
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
