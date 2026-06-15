<?php

namespace App\Http\Resources\Api\V1;

class TypicalApplicationResource extends CatalogMasterResource
{
    protected function extraFields(): array
    {
        return [
            'description' => $this->description,
        ];
    }
}
