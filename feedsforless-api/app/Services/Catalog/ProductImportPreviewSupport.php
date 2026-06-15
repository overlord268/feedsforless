<?php

namespace App\Services\Catalog;

trait ProductImportPreviewSupport
{
    private bool $dryRun = false;

    /** @var array<string, string>|null map preview key => apply|skip */
    private ?array $decisions = null;

    /** @var array<string, array<string, true>> */
    private array $seenSlugsInFile = [];

    private function previewKey(string $entity, string $slug): string
    {
        return $entity.':'.$slug;
    }

    private function isDuplicateInFile(
        string $entity,
        string $slug,
        int $rowNum,
        string $sheet,
        ProductImportResult $result
    ): bool {
        if (isset($this->seenSlugsInFile[$entity][$slug])) {
            $result->addError($sheet, $rowNum, "Duplicate slug \"{$slug}\" in this file. Only the first row is processed.");
            $result->addPreview([
                'key' => $this->previewKey($entity, $slug).':dup:'.$rowNum,
                'entity' => $entity,
                'sheet' => $sheet,
                'row' => $rowNum,
                'slug' => $slug,
                'label' => $slug,
                'action' => 'skip',
                'status' => 'error',
                'recommended' => 'skip',
                'conflicts' => ['Duplicate slug in this file'],
                'existing' => null,
                'incoming' => [],
                'details' => null,
            ]);

            return true;
        }

        $this->seenSlugsInFile[$entity][$slug] = true;

        return false;
    }

    private function resolveDecision(string $key, string $recommended): string
    {
        if ($this->decisions !== null && array_key_exists($key, $this->decisions)) {
            return $this->decisions[$key] === 'apply' ? 'apply' : 'skip';
        }

        return $recommended;
    }

    private function shouldApply(string $key, string $recommended): bool
    {
        return $this->resolveDecision($key, $recommended) === 'apply';
    }

    /**
     * @param  list<string>  $conflicts
     */
    private function recommendedDecision(string $action, array $conflicts): string
    {
        if ($action === 'skip' || $action === 'error') {
            return 'skip';
        }

        if ($conflicts !== []) {
            return 'skip';
        }

        return 'apply';
    }

    /**
     * @param  list<string>  $fields
     * @return list<string>
     */
    private function diffFields(array $existing, array $incoming, array $fields): array
    {
        $conflicts = [];

        foreach ($fields as $field) {
            $old = $existing[$field] ?? null;
            $new = $incoming[$field] ?? null;

            if ($this->fieldValuesEqual($old, $new)) {
                continue;
            }

            $conflicts[] = sprintf(
                '%s: "%s" → "%s"',
                $field,
                $this->formatFieldValue($old),
                $this->formatFieldValue($new)
            );
        }

        return $conflicts;
    }

    private function fieldValuesEqual(mixed $old, mixed $new): bool
    {
        $oldEmpty = $old === null || $old === '';
        $newEmpty = $new === null || $new === '';

        if ($oldEmpty && $newEmpty) {
            return true;
        }

        if ($oldEmpty || $newEmpty) {
            return false;
        }

        if (is_numeric($old) && is_numeric($new)) {
            return (float) $old === (float) $new;
        }

        return (string) $old === (string) $new;
    }

    private function formatFieldValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_numeric($value)) {
            $float = (float) $value;

            return fmod($float, 1.0) === 0.0 ? (string) (int) $float : rtrim(rtrim(sprintf('%.4F', $float), '0'), '.');
        }

        return (string) $value;
    }

    /**
     * @param  list<string>  $conflicts
     * @param  array<string, mixed>|null  $existing
     * @param  array<string, mixed>  $incoming
     */
    private function recordPreview(
        ProductImportResult $result,
        string $entity,
        string $sheet,
        int $row,
        string $slug,
        string $label,
        string $action,
        array $conflicts,
        ?array $existing = null,
        array $incoming = [],
        ?string $details = null
    ): string {
        $status = match (true) {
            $action === 'error' => 'error',
            $conflicts !== [] => 'conflict',
            default => 'ok',
        };

        $recommended = $this->recommendedDecision($action, $conflicts);
        $key = $this->previewKey($entity, $slug);

        $result->addPreview([
            'key' => $key,
            'entity' => $entity,
            'sheet' => $sheet,
            'row' => $row,
            'slug' => $slug,
            'label' => $label,
            'action' => $action,
            'status' => $status,
            'recommended' => $recommended,
            'conflicts' => $conflicts,
            'existing' => $existing,
            'incoming' => $incoming,
            'details' => $details,
        ]);

        return $key;
    }

    private function persistOrPreview(
        ProductImportResult $result,
        string $previewKey,
        string $recommended,
        string $summaryGroup,
        string $action,
        callable $persist
    ): void {
        $decision = $this->resolveDecision($previewKey, $recommended);

        if ($this->dryRun) {
            if ($decision === 'apply') {
                $result->bump($summaryGroup, $action === 'create' ? 'created' : 'updated');
            } else {
                $result->bump($summaryGroup, 'skipped');
            }

            return;
        }

        if ($decision !== 'apply') {
            $result->bump($summaryGroup, 'skipped');

            return;
        }

        $persist();
        $result->bump($summaryGroup, $action === 'create' ? 'created' : 'updated');
    }
}
