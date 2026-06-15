<?php

namespace App\Services\Catalog;

use App\Models\FflSkuCategoryMap;
use App\Models\FflSkuMapAudit;
use App\Models\FflSkuProductMap;
use App\Domains\B2B\Models\User;
use Illuminate\Support\Facades\DB;

class FflSkuMapService
{
    public function __construct(
        private readonly FflSkuMapRepository $mapRepository
    ) {}

    /**
     * @return array{categories: list<array{id: int, label: string, code: string}>, products: list<array{id: int, product_name: string, code: string}>, can_edit: bool}
     */
    public function snapshot(bool $canEdit): array
    {
        return [
            'categories' => FflSkuCategoryMap::query()->orderBy('label')->get(['id', 'label', 'code'])->all(),
            'products' => FflSkuProductMap::query()->orderBy('product_name')->get(['id', 'product_name', 'code'])->all(),
            'prefix' => config('ffl_sku.prefix', 'FFL'),
            'can_edit' => $canEdit,
        ];
    }

    public function createCategory(array $data, User $user, ?string $ip): FflSkuCategoryMap
    {
        return DB::transaction(function () use ($data, $user, $ip) {
            $map = FflSkuCategoryMap::query()->create([
                'label' => $data['label'],
                'code' => strtoupper($data['code']),
            ]);

            $this->audit($user, 'category', 'created', null, $map->only(['id', 'label', 'code']), $ip);
            $this->mapRepository->forgetCache();

            return $map;
        });
    }

    public function updateCategory(FflSkuCategoryMap $map, array $data, User $user, ?string $ip): FflSkuCategoryMap
    {
        return DB::transaction(function () use ($map, $data, $user, $ip) {
            $before = $map->only(['id', 'label', 'code']);
            $map->update([
                'label' => $data['label'],
                'code' => strtoupper($data['code']),
            ]);
            $this->audit($user, 'category', 'updated', $before, $map->fresh()->only(['id', 'label', 'code']), $ip);
            $this->mapRepository->forgetCache();

            return $map->fresh();
        });
    }

    public function deleteCategory(FflSkuCategoryMap $map, User $user, ?string $ip): void
    {
        DB::transaction(function () use ($map, $user, $ip) {
            $before = $map->only(['id', 'label', 'code']);
            $map->delete();
            $this->audit($user, 'category', 'deleted', $before, null, $ip);
            $this->mapRepository->forgetCache();
        });
    }

    public function createProduct(array $data, User $user, ?string $ip): FflSkuProductMap
    {
        return DB::transaction(function () use ($data, $user, $ip) {
            $map = FflSkuProductMap::query()->create([
                'product_name' => $data['product_name'],
                'code' => strtoupper($data['code']),
            ]);

            $this->audit($user, 'product', 'created', null, $map->only(['id', 'product_name', 'code']), $ip);
            $this->mapRepository->forgetCache();

            return $map;
        });
    }

    public function updateProduct(FflSkuProductMap $map, array $data, User $user, ?string $ip): FflSkuProductMap
    {
        return DB::transaction(function () use ($map, $data, $user, $ip) {
            $before = $map->only(['id', 'product_name', 'code']);
            $map->update([
                'product_name' => $data['product_name'],
                'code' => strtoupper($data['code']),
            ]);
            $this->audit($user, 'product', 'updated', $before, $map->fresh()->only(['id', 'product_name', 'code']), $ip);
            $this->mapRepository->forgetCache();

            return $map->fresh();
        });
    }

    public function deleteProduct(FflSkuProductMap $map, User $user, ?string $ip): void
    {
        DB::transaction(function () use ($map, $user, $ip) {
            $before = $map->only(['id', 'product_name', 'code']);
            $map->delete();
            $this->audit($user, 'product', 'deleted', $before, null, $ip);
            $this->mapRepository->forgetCache();
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentAudits(int $limit = 50): array
    {
        return FflSkuMapAudit::query()
            ->with('user:id,first_name,last_name,email')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (FflSkuMapAudit $audit) => [
                'id' => $audit->id,
                'map_type' => $audit->map_type,
                'action' => $audit->action,
                'before' => $audit->before,
                'after' => $audit->after,
                'ip_address' => $audit->ip_address,
                'created_at' => $audit->created_at?->toIso8601String(),
                'user' => $audit->user ? [
                    'id' => $audit->user->id,
                    'name' => trim($audit->user->first_name.' '.$audit->user->last_name),
                    'email' => $audit->user->email,
                ] : null,
            ])
            ->all();
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    private function audit(User $user, string $mapType, string $action, ?array $before, ?array $after, ?string $ip): void
    {
        FflSkuMapAudit::query()->create([
            'user_id' => $user->id,
            'map_type' => $mapType,
            'action' => $action,
            'before' => $before,
            'after' => $after,
            'ip_address' => $ip,
            'created_at' => now(),
        ]);
    }
}
