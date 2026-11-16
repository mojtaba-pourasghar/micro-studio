<?php

namespace App\Console\Commands;

use App\Models\AgentToken;
use Illuminate\Console\Command;

class CreateAgentToken extends Command
{
    protected $signature = 'ms:token {name=Claude} {--publish : Allow publishing posts without approval}';

    protected $description = 'Create an agent token for the MCP server and print the connector URL';

    public function handle(): int
    {
        $abilities = array_values(array_diff(array_keys(AgentToken::ABILITIES), AgentToken::RESTRICTED));
        if ($this->option('publish')) {
            $abilities[] = 'posts.publish';
        }
        [, $plain] = AgentToken::issue($this->argument('name'), $abilities);
        $this->line(url('/mcp/'.$plain));
        $this->line('Bearer token: '.$plain);

        return self::SUCCESS;
    }
}
