<?php

namespace App\Http\Resources\Api\V1;

class HandlingSpecResource extends CatalogMasterResource
{
    protected function extraFields(): array
    {
        return [
            'requirement' => $this->requirement,
        ];
    }
}
