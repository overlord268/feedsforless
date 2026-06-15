<?php

namespace App\Console\Commands;

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\HandlingSpec;
use App\Domains\Catalog\Models\NutritionalParameter;
use App\Domains\Catalog\Models\PackagingType;
use App\Domains\Catalog\Models\Parameter;
use App\Domains\Catalog\Models\ProductPackaging;
use App\Domains\Catalog\Models\TestMethod;
use App\Domains\Catalog\Models\TypicalApplication;
use App\Http\Controllers\Api\V1\Admin\AdminCatalogsController;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class RollbackImportExampleMasters extends Command
{
    protected $signature = 'catalog:rollback-import-example-masters
                            {--dry-run : List masters that would be deleted without changing the database}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Remove catalog masters created by the product import template example (premium-lysine-hcl)';

    /** @var array<string, list<string>> */
    private const MASTER_SLUGS = [
        'packaging_types' => ['50-lb-bag-lysine', 'tote-2000-lb'],
        'parameters' => ['purity-lysine'],
        'test_methods' => ['usp-891'],
        'nutritional_parameters' => ['lysine'],
        'handling_specs' => ['keep-sealed'],
        'typical_applications' => ['aqua-feed'],
        'categories' => ['amino-acids'],
    ];

    /** @var array<string, class-string<Model>> */
    private const MODEL_MAP = [
        'packaging_types' => PackagingType::class,
        'parameters' => Parameter::class,
        'test_methods' => TestMethod::class,
        'nutritional_parameters' => NutritionalParameter::class,
        'handling_specs' => HandlingSpec::class,
        'typical_applications' => TypicalApplication::class,
        'categories' => Category::class,
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $plan = $this->buildPlan();

        if ($plan['found'] === []) {
            $this->info('No import-example masters found in the database (already removed or never imported).');

            return self::SUCCESS;
        }

        $this->table(
            ['Type', 'Slug', 'Label', 'Status'],
            array_map(
                static fn (array $row) => [$row['type'], $row['slug'], $row['label'], $row['status']],
                $plan['rows']
            )
        );

        if ($plan['blocked'] !== []) {
            $this->newLine();
            $this->warn('Some masters are still referenced and cannot be deleted:');
            foreach ($plan['blocked'] as $message) {
                $this->line("  - {$message}");
            }
        }

        if ($plan['deletable'] === []) {
            return self::FAILURE;
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('[dry-run] Would delete '.count($plan['deletable']).' master record(s).');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Delete the deletable masters listed above?', false)) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $deleted = 0;
        foreach ($plan['deletable'] as $item) {
            /** @var Model $model */
            $model = $item['model'];
            $model->delete();
            $deleted++;
            $this->line("Deleted {$item['type']} \"{$item['slug']}\".");
        }

        AdminCatalogsController::forgetCache();

        $this->newLine();
        $this->info("Done. Deleted {$deleted} master record(s).");

        return self::SUCCESS;
    }

    /**
     * @return array{
     *     rows: list<array{type: string, slug: string, label: string, status: string}>,
     *     found: list<array<string, mixed>>,
     *     deletable: list<array<string, mixed>>,
     *     blocked: list<string>
     * }
     */
    private function buildPlan(): array
    {
        $rows = [];
        $found = [];
        $deletable = [];
        $blocked = [];

        foreach (self::MASTER_SLUGS as $type => $slugs) {
            $modelClass = self::MODEL_MAP[$type];

            foreach ($slugs as $slug) {
                /** @var Model|null $model */
                $model = $modelClass::query()->where('slug', $slug)->first();

                if ($model === null) {
                    continue;
                }

                $label = $this->masterLabel($model);
                $blockReason = $this->blockReason($type, $model);

                $entry = [
                    'type' => $type,
                    'slug' => $slug,
                    'label' => $label,
                    'model' => $model,
                ];

                $found[] = $entry;

                if ($blockReason !== null) {
                    $rows[] = [
                        'type' => $type,
                        'slug' => $slug,
                        'label' => $label,
                        'status' => 'blocked',
                    ];
                    $blocked[] = "{$type}/{$slug}: {$blockReason}";

                    continue;
                }

                $rows[] = [
                    'type' => $type,
                    'slug' => $slug,
                    'label' => $label,
                    'status' => 'will delete',
                ];
                $deletable[] = $entry;
            }
        }

        return compact('rows', 'found', 'deletable', 'blocked');
    }

    private function masterLabel(Model $model): string
    {
        return (string) ($model->label ?? $model->name ?? $model->slug ?? $model->getKey());
    }

    private function blockReason(string $type, Model $model): ?string
    {
        return match ($type) {
            'packaging_types' => ProductPackaging::query()
                ->where('packaging_type_id', $model->getKey())
                ->exists()
                ? 'still used by product packaging'
                : null,
            'categories' => $model instanceof Category && (
                $model->products()->exists() || $model->children()->exists()
            )
                ? 'still linked to products or child categories'
                : null,
            'handling_specs' => method_exists($model, 'products') && $model->products()->exists()
                ? 'still linked to products'
                : null,
            'typical_applications' => method_exists($model, 'products') && $model->products()->exists()
                ? 'still linked to products'
                : null,
            default => null,
        };
    }
}
