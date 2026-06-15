<?php

namespace App\Http\Resources\Concerns;

use App\Http\Resources\DataOnlyResourceCollection;

trait CollectsDataOnly
{
    public static function dataOnlyCollection($resource): DataOnlyResourceCollection
    {
        return new DataOnlyResourceCollection($resource, static::class);
    }
}
