<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreAgentApiTokenRequest;
use App\Models\AgentApiToken;
use App\Services\AgentApiTokenService;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAgentApiTokenController extends Controller
{
    public function __construct(
        private readonly AgentApiTokenService $tokenService
    ) {}

    public function index(): JsonResponse
    {
        $tokens = AgentApiToken::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (AgentApiToken $token) => [
                'id' => $token->id,
                'name' => $token->name,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'is_active' => $token->isActive(),
                'created_at' => $token->created_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $tokens], 200);
    }

    /**
     * Create agent token. The `plain_token` field is returned once and cannot be retrieved later.
     */
    #[Response(201, 'Token created. Copy plain_token immediately — it is shown once only.', type: 'array{message: string, data: array, plain_token: string}')]
    public function store(StoreAgentApiTokenRequest $request): JsonResponse
    {
        $result = $this->tokenService->create(
            $request->validated('name'),
            $request->user()?->id
        );

        return response()->json([
            'message' => 'Agent API token created. Copy the token now; it will not be shown again.',
            'data' => $this->tokenResource($result['token']),
            'plain_token' => $result['plain_token'],
        ], 201);
    }

    public function destroy(AgentApiToken $agentApiToken): JsonResponse
    {
        $this->tokenService->revoke($agentApiToken);

        return response()->json([
            'message' => 'Agent API token revoked.',
            'data' => $this->tokenResource($agentApiToken->fresh()),
        ], 200);
    }

    /**
     * Rotate agent token. The new `plain_token` is returned once and cannot be retrieved later.
     */
    #[Response(200, 'Token rotated. Copy plain_token immediately — it is shown once only.', type: 'array{message: string, data: array, plain_token: string}')]
    public function rotate(Request $request, AgentApiToken $agentApiToken): JsonResponse
    {
        if (! $agentApiToken->isActive()) {
            return response()->json([
                'message' => 'Cannot rotate a revoked token.',
            ], 422);
        }

        $result = $this->tokenService->rotate($agentApiToken, $request->user()?->id);

        return response()->json([
            'message' => 'Agent API token rotated. Copy the new token now; it will not be shown again.',
            'data' => $this->tokenResource($result['token']),
            'plain_token' => $result['plain_token'],
        ], 200);
    }

    /**
     * @return array<string, mixed>
     */
    private function tokenResource(AgentApiToken $token): array
    {
        return [
            'id' => $token->id,
            'name' => $token->name,
            'last_used_at' => $token->last_used_at?->toIso8601String(),
            'is_active' => $token->isActive(),
            'created_at' => $token->created_at?->toIso8601String(),
        ];
    }
}
