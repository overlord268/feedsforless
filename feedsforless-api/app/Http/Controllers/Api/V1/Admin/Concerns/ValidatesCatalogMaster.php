<?php

namespace App\Http\Controllers\Api\V1\Admin\Concerns;

use Illuminate\Validation\Rule;

trait ValidatesCatalogMaster
{
    /**
     * @return array<string, list<mixed>>
     */
    protected function catalogSlugRules(string $table, ?int $ignoreId = null): array
    {
        return [
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique($table, 'slug')->ignore($ignoreId),
            ],
        ];
    }
}
