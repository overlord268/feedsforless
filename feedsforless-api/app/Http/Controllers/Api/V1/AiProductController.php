<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAiProductRequest;
use App\Services\Catalog\AiProductService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('AI Agent API', description: 'Machine-to-machine endpoints for external AI agents. Requires agent API token.', weight: 0)]
class AiProductController extends Controller
{
    public function __construct(
        private readonly AiProductService $aiProductService
    ) {}

    /**
     * Create or update a product by slug (upsert). All master references use slugs from GET /masters.
     */
    public function store(StoreAiProductRequest $request): JsonResponse
    {
        $result = $this->aiProductService->upsert($request->validated());

        $status = $result['created'] ? 201 : 200;

        return response()->json([
            'message' => $result['created']
                ? 'Product created successfully'
                : 'Product updated successfully',
            'action' => $result['created'] ? 'created' : 'updated',
            'data' => $result['resource'],
        ], $status);
    }
}
