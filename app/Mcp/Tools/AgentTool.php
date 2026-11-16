<?php

namespace App\Mcp\Tools;

use App\Support\Actor;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

abstract class AgentTool extends Tool
{
    /** snake_case tool names (site_overview, save_item…) as referenced in the server instructions. */
    public function name(): string
    {
        return \Illuminate\Support\Str::snake(class_basename($this));
    }

    /** Returns an error Response when the current token lacks the ability, otherwise null. */
    protected function deny(string $ability): ?Response
    {
        $token = Actor::agent();
        if (! $token || ! $token->allows($ability)) {
            return Response::error("Permission denied: this token lacks the \"$ability\" ability. Ask the site owner to grant it in the admin (اتصال کلاد).");
        }

        return null;
    }

    protected function json(mixed $data): Response
    {
        return Response::text(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
}
