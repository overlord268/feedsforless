<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Concerns\CollectsDataOnly;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackagingTypeResource extends JsonResource
{
    use CollectsDataOnly;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
