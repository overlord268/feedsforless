<?php

namespace App\Console\Commands;

use App\Domains\Catalog\Models\Product;
use App\Services\Catalog\FflSkuGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BackfillFflProductSkus extends Command
{
    protected $signature = 'products:backfill-ffl-skus
                            {--dry-run : Preview changes without writing to the database}
                            {--force : Apply without confirmation prompt}';

    protected $description = 'Regenerate FFL SKUs for existing products from category, name, and grade';

    public function handle(FflSkuGenerator $generator): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $products = Product::query()
            ->with(['categories.parent.parent'])
            ->orderBy('id')
            ->get();

        if ($products->isEmpty()) {
            $this->info('No products found.');

            return self::SUCCESS;
        }

        $targets = [];
        $errors = [];

        foreach ($products as $product) {
            try {
                $targets[$product->id] = $generator->generateForProduct($product);
            } catch (InvalidArgumentException $e) {
                $errors[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'current' => $product->sku,
                    'message' => $e->getMessage(),
                ];
            }
        }

        $updates = $this->buildUpdates($products, $targets);
        $skipped = $products->count() - count($updates) - count($errors);

        if ($updates === []) {
            $this->info("All mappable products already have FFL SKUs ({$skipped} unchanged).");

            if ($errors !== []) {
                $this->printErrors($errors);
            }

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Current SKU', 'New FFL SKU'],
            collect($updates)->map(fn (array $row) => [
                $row['id'],
                $row['name'],
                $row['current'],
                $row['new'],
            ])->all()
        );

        if ($dryRun) {
            $this->info('Dry run — no changes written.');

            if ($errors !== []) {
                $this->printErrors($errors);
            }

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Apply these SKU updates?', true)) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($updates): void {
            foreach ($updates as $row) {
                Product::query()
                    ->whereKey($row['id'])
                    ->update(['sku' => '__MIGRATE_'.$row['id'].'__']);
            }

            foreach ($updates as $row) {
                Product::query()
                    ->whereKey($row['id'])
                    ->update(['sku' => $row['new']]);
            }
        });

        $this->info(count($updates).' product SKU(s) updated. '.$skipped.' unchanged.');

        if ($errors !== []) {
            $this->printErrors($errors);
        }

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  array<int, string>  $targets
     * @return list<array{id: int, name: string, current: string, new: string}>
     */
    private function buildUpdates(Collection $products, array $targets): array
    {
        $byTarget = collect($targets)
            ->mapToGroups(fn (string $sku, int $id) => [$sku => $products->firstWhere('id', $id)])
            ->map(fn (Collection $group) => $this->pickWinner($group));

        $winnerByTarget = $byTarget;
        $updates = [];

        foreach ($products as $product) {
            if (! isset($targets[$product->id])) {
                continue;
            }

            $targetSku = $targets[$product->id];
            $winnerId = $winnerByTarget[$targetSku] ?? $product->id;

            if ($product->id === $winnerId) {
                $newSku = $targetSku;
            } else {
                $newSku = '__DUPLICATE_'.$product->id.'__';
            }

            if ($product->sku === $newSku) {
                continue;
            }

            $updates[] = [
                'id' => $product->id,
                'name' => $product->name,
                'current' => $product->sku,
                'new' => $newSku,
            ];
        }

        return $updates;
    }

    /**
     * @param  Collection<int, Product>  $group
     */
    private function pickWinner(Collection $group): int
    {
        $sorted = $group->sortBy(function (Product $product): array {
            return [
                $product->status === 'published' ? 0 : 1,
                $product->id,
            ];
        });

        return (int) $sorted->first()->id;
    }

    /**
     * @param  list<array{id: int, name: string, current: string, message: string}>  $errors
     */
    private function printErrors(array $errors): void
    {
        $this->newLine();
        $this->warn('Products skipped (could not generate FFL SKU):');
        $this->table(
            ['ID', 'Name', 'Current SKU', 'Reason'],
            collect($errors)->map(fn (array $row) => [
                $row['id'],
                $row['name'],
                $row['current'],
                $row['message'],
            ])->all()
        );
    }
}
