<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Wraps a paginator or collection as { "data": [...] } without links/meta.
 */
class DataOnlyResourceCollection extends ResourceCollection
{
    public function __construct($resource, ?string $collects = null)
    {
        if ($collects !== null) {
            $this->collects = $collects;
        }

        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }

    public function toResponse($request): JsonResponse
    {
        return response()->json(['data' => $this->toArray($request)]);
    }
}
