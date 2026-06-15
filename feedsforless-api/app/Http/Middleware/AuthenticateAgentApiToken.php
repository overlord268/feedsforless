<?php

namespace App\Http\Middleware;

use App\Services\AgentApiTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAgentApiToken
{
    public function __construct(
        private readonly AgentApiTokenService $tokenService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();

        if ($plainToken === null || $plainToken === '') {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $token = $this->tokenService->findActiveByPlainToken($plainToken);

        if ($token === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $token->touchLastUsed();
        $request->attributes->set('agent_api_token', $token);

        return $next($request);
    }
}
