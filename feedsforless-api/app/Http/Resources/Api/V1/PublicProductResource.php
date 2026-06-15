<?php

namespace App\Http\Resources\Api\V1;

use App\Services\Catalog\ProfitMarginService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public catalog product — prices include profit margin; margin is never exposed.
 */
class PublicProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $payload = (new ProductResource($this->resource))->resolve($request);

        if (! is_array($payload)) {
            return $payload;
        }

        return app(ProfitMarginService::class)->applyMarginsToPublicPayload($payload, $this->resource);
    }
}
