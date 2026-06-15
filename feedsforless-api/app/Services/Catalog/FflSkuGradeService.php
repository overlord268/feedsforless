<?php

namespace App\Services\Catalog;

use App\Domains\B2B\Models\User;
use App\Models\FflSkuGrade;
use App\Models\FflSkuMapAudit;
use Illuminate\Support\Facades\DB;

class FflSkuGradeService
{
    public function __construct(
        private readonly FflSkuGradeRepository $gradeRepository
    ) {}

    /**
     * @return array{grades: list<FflSkuGrade>, auto_fields: array<string, string>}
     */
    public function snapshot(): array
    {
        return [
            'grades' => $this->gradeRepository->all()->values()->all(),
            'auto_fields' => [
                'cat' => 'Automatic from category (slug/name)',
                'prod' => 'Automatic from product name',
                'grade' => 'Match product Grade field to a registered grade below → SKU suffix',
            ],
        ];
    }

    public function create(array $data, User $user, ?string $ip): FflSkuGrade
    {
        return DB::transaction(function () use ($data, $user, $ip) {
            $grade = FflSkuGrade::query()->create([
                'grade_spec' => $data['grade_spec'],
                'sku_code' => strtoupper($data['sku_code']),
            ]);
            $this->audit($user, 'created', null, $grade->toArray(), $ip);
            $this->gradeRepository->forgetCache();

            return $grade;
        });
    }

    public function update(FflSkuGrade $grade, array $data, User $user, ?string $ip): FflSkuGrade
    {
        return DB::transaction(function () use ($grade, $data, $user, $ip) {
            $before = $grade->toArray();
            $grade->update([
                'grade_spec' => $data['grade_spec'],
                'sku_code' => strtoupper($data['sku_code']),
            ]);
            $this->audit($user, 'updated', $before, $grade->fresh()->toArray(), $ip);
            $this->gradeRepository->forgetCache();

            return $grade->fresh();
        });
    }

    public function delete(FflSkuGrade $grade, User $user, ?string $ip): void
    {
        DB::transaction(function () use ($grade, $user, $ip) {
            $before = $grade->toArray();
            $grade->delete();
            $this->audit($user, 'deleted', $before, null, $ip);
            $this->gradeRepository->forgetCache();
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentAudits(int $limit = 50): array
    {
        return FflSkuMapAudit::query()
            ->where('map_type', 'grade')
            ->with('user:id,first_name,last_name,email')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (FflSkuMapAudit $audit) => [
                'id' => $audit->id,
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
    private function audit(User $user, string $action, ?array $before, ?array $after, ?string $ip): void
    {
        FflSkuMapAudit::query()->create([
            'user_id' => $user->id,
            'map_type' => 'grade',
            'action' => $action,
            'before' => $before,
            'after' => $after,
            'ip_address' => $ip,
            'created_at' => now(),
        ]);
    }
}
