<?php

namespace App\Services\Catalog;

class ProductImportResult
{
    /** @var list<array{sheet: string, row: int, message: string}> */
    public array $errors = [];

    /** @var array<string, array{created: int, updated: int, skipped: int}> */
    public array $summary = [];

    /** @var list<array<string, mixed>> */
    public array $preview = [];

    public bool $dryRun = false;

    public function addError(string $sheet, int $row, string $message): void
    {
        $this->errors[] = [
            'sheet' => $sheet,
            'row' => $row,
            'message' => $message,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public function addPreview(array $item): void
    {
        $this->preview[] = $item;
    }

    public function bump(string $group, string $action): void
    {
        if (! isset($this->summary[$group])) {
            $this->summary[$group] = ['created' => 0, 'updated' => 0, 'skipped' => 0];
        }

        if (! isset($this->summary[$group][$action])) {
            $this->summary[$group][$action] = 0;
        }

        $this->summary[$group][$action]++;
    }

    public function toArray(): array
    {
        $conflictCount = count(array_filter(
            $this->preview,
            static fn (array $item) => ($item['status'] ?? '') === 'conflict'
        ));

        return [
            'dry_run' => $this->dryRun,
            'success' => $this->errors === [],
            'summary' => $this->summary,
            'preview' => $this->preview,
            'preview_stats' => [
                'total' => count($this->preview),
                'conflicts' => $conflictCount,
                'creates' => count(array_filter($this->preview, static fn ($i) => ($i['action'] ?? '') === 'create')),
                'updates' => count(array_filter($this->preview, static fn ($i) => ($i['action'] ?? '') === 'update')),
            ],
            'errors' => $this->errors,
        ];
    }
}
