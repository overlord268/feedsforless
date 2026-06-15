<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AttemptSanctumAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken() && !$request->user('sanctum')) {
            $accessToken = PersonalAccessToken::findToken($request->bearerToken());
            if ($accessToken?->tokenable) {
                Auth::guard('sanctum')->setUser($accessToken->tokenable);
            }
        }

        return $next($request);
    }
}
