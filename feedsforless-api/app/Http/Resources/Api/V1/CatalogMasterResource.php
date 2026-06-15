<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Concerns\CollectsDataOnly;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Base shape for catalog master records (parameters, test methods, etc.).
 */
abstract class CatalogMasterResource extends JsonResource
{
    use CollectsDataOnly;

    public function toArray(Request $request): array
    {
        return array_merge([
            'id' => $this->id,
            'label' => $this->label,
            'slug' => $this->slug,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ], $this->extraFields());
    }

    /**
     * @return array<string, mixed>
     */
    protected function extraFields(): array
    {
        return [];
    }
}
