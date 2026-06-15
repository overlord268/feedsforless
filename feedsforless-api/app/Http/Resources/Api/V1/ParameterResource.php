<?php

namespace App\Http\Resources\Api\V1;

class ParameterResource extends CatalogMasterResource
{
    protected function extraFields(): array
    {
        return [
            'type' => $this->type,
        ];
    }
}
