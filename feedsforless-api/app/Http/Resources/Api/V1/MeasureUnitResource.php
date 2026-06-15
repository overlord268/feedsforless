<?php

namespace App\Http\Resources\Api\V1;

class MeasureUnitResource extends CatalogMasterResource
{
    protected function extraFields(): array
    {
        return [
            'notation' => $this->notation,
        ];
    }
}
