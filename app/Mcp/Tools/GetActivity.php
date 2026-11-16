<?php

namespace App\Mcp\Tools;

use App\Models\ActivityLog;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Recent changes to the site (by the owner or agents) with before/after values. Use the log id with rollback_change to undo.')]
#[IsReadOnly]
class GetActivity extends AgentTool
{
    public function handle(Request $request): Response
    {
        $q = ActivityLog::query()->latest('id')->limit(min(100, max(1, (int) ($request->get('limit') ?: 20))));
        if ($request->get('only_agent')) {
            $q->where('actor', 'like', 'agent:%');
        }

        return $this->json($q->get(['id', 'actor', 'action', 'subject_type', 'subject_id', 'summary', 'before', 'after', 'created_at']));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()->description('Default 20, max 100.'),
            'only_agent' => $schema->boolean()->description('Only changes made by agent tokens.'),
        ];
    }
}
