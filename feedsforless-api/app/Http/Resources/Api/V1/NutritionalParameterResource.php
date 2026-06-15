<?php

namespace App\Http\Resources\Api\V1;

class NutritionalParameterResource extends CatalogMasterResource
{
    protected function extraFields(): array
    {
        return [
            'notation' => $this->notation,
        ];
    }
}
