<?php

namespace App\Mcp\Tools;

use App\Models\ContentRule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Content rules approved by the site owner. You MUST read and follow every approved rule before creating or editing any content. Proposed rules are listed separately and are NOT yet binding.')]
#[IsReadOnly]
class GetContentRules extends AgentTool
{
    public function handle(Request $request): Response
    {
        return $this->json([
            'approved' => ContentRule::where('status', 'approved')->orderBy('sort_order')->get(['id', 'title', 'body']),
            'proposed_waiting_for_owner' => ContentRule::where('status', 'proposed')->orderBy('sort_order')->get(['id', 'title', 'body']),
        ]);
    }
}
