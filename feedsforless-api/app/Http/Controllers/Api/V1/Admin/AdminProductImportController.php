<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\ImportProductsRequest;
use App\Services\Catalog\ProductImportService;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminProductImportController extends Controller
{
    /**
     * Download the Excel product import template.
     */
    #[Response(200, 'Excel import template file.', mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')]
    public function template(): BinaryFileResponse
    {
        $path = base_path('../docs/product-import-template-v2-feedsforless.xlsx');

        if (! is_file($path)) {
            abort(404, 'Import template file not found. Run: python scripts/generate-product-import-template2.py');
        }

        return response()->download(
            $path,
            'product-import-template-feedsforless.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function import(ImportProductsRequest $request, ProductImportService $importService): JsonResponse
    {
        $dryRun = $request->boolean('dry_run');
        $decisions = $request->input('decisions');
        $decisions = is_array($decisions) ? $decisions : null;

        $result = $importService->import(
            $request->file('file')->getRealPath(),
            $dryRun,
            $decisions
        );

        $success = $result->errors === [];
        $message = $success
            ? ($dryRun
                ? 'Dry run completed — review each row below and choose Apply or Skip before importing.'
                : 'Import completed successfully.')
            : ($dryRun ? 'Dry run finished with validation errors.' : 'Import finished with errors.');

        return response()->json(array_merge(
            ['message' => $message],
            $result->toArray()
        ), $success ? 200 : 422);
    }
}
