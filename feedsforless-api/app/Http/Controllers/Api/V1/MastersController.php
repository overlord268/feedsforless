<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Catalog\CatalogMastersYamlExporter;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Cache;

#[Group('AI Agent API', description: 'Machine-to-machine endpoints for external AI agents. Requires agent API token.', weight: 0)]
class MastersController extends Controller
{
    private const CACHE_KEY = 'agent:masters:yaml';

    private const CACHE_TTL_SECONDS = 300;

    public function __construct(
        private readonly CatalogMastersYamlExporter $exporter
    ) {}

    /**
     * Returns catalog master records and field constraints as YAML.
     * Call this before POST /ai/products so the agent only references valid slugs.
     */
    #[QueryParameter('include_products', description: 'When true, includes a slim product index for related-product slugs.', type: 'boolean', required: false)]
    #[Response(200, 'Catalog masters YAML document with meta, constraints, and master sections.', mediaType: 'text/yaml')]
    public function index(Request $request): HttpResponse
    {
        $includeProducts = $request->boolean('include_products');
        $cacheKey = self::CACHE_KEY.($includeProducts ? ':with_products' : '');

        $yaml = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($includeProducts) {
            return $this->exporter->export($includeProducts);
        });

        return response($yaml, 200, [
            'Content-Type' => 'text/yaml; charset=UTF-8',
        ]);
    }
}
