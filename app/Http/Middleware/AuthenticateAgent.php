<?php

namespace App\Http\Middleware;

use App\Models\AgentToken;
use App\Support\Actor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Authenticates the AI agent by token: /mcp/{token} in the URL, or "Authorization: Bearer <token>". */
class AuthenticateAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->route('token') ?: $request->bearerToken();
        $token = AgentToken::findByPlain(is_string($plain) ? $plain : null);
        if (! $token) {
            return response()->json(['error' => 'Invalid or revoked agent token.'], 401);
        }
        $request->route()?->forgetParameter('token');
        if (! $token->last_used_at || $token->last_used_at->lt(now()->subMinute())) {
            $token->forceFill(['last_used_at' => now()])->saveQuietly();
        }
        Actor::setAgent($token);
        try {
            return $next($request);
        } finally {
            Actor::setAgent(null);
        }
    }
}
