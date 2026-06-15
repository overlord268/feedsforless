<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domains\Catalog\Models\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\UploadProductDocumentRequest;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminProductDocumentController extends Controller
{
    public function upload(UploadProductDocumentRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $type = $request->input('type');
        $ext = $file->getClientOriginalExtension() ?: 'pdf';
        $name = Str::uuid().'_'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.$ext;

        $stored = $file->storeAs('product-documents/'.$type, $name, 'local');

        return response()->json([
            'message' => 'Document uploaded successfully',
            'path' => $stored,
        ], 201);
    }

    /**
     * Download product TDS, SDS, or COA document.
     */
    #[Response(200, 'Document file stream.', mediaType: 'application/octet-stream')]
    public function download(Product $product, string $type): StreamedResponse|JsonResponse
    {
        if (! in_array($type, ['tds', 'sds', 'coa'], true)) {
            return response()->json(['message' => 'Invalid document type'], 400);
        }
        $pathKey = $type.'_document_path';
        $path = $product->$pathKey;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        return Storage::disk('local')->download($path, basename($path), [
            'Content-Type' => Storage::disk('local')->mimeType($path) ?: 'application/octet-stream',
        ]);
    }
}
