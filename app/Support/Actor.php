<?php

namespace App\Support;

use App\Models\AgentToken;

/** Who is changing content right now: an agent token (MCP / REST) or a logged-in admin. */
class Actor
{
    protected static ?AgentToken $agent = null;

    public static function setAgent(?AgentToken $token): void
    {
        static::$agent = $token;
    }

    public static function agent(): ?AgentToken
    {
        return static::$agent;
    }

    public static function isAgent(): bool
    {
        return static::$agent !== null;
    }

    public static function label(): string
    {
        if (static::$agent) {
            return 'agent:'.static::$agent->name;
        }
        $user = auth()->user();

        return $user ? 'admin:'.$user->email : 'system';
    }
}
