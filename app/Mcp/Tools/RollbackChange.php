<?php

namespace App\Mcp\Tools;

use App\Models\ActivityLog;
use App\Support\Rollback;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Undo one change from get_activity: an update returns to the previous values, a delete is restored, a creation is (soft) deleted. Agents can only roll back changes made by agents.')]
class RollbackChange extends AgentTool
{
    public function handle(Request $request): Response
    {
        if ($denied = $this->deny('content.write')) {
            return $denied;
        }
        $log = ActivityLog::find((int) $request->get('log_id'));
        if (! $log) {
            return Response::error('Log entry not found.');
        }
        if (! str_starts_with($log->actor, 'agent:')) {
            return Response::error('Only changes made by agents can be rolled back here; the owner can roll back anything in the admin.');
        }
        $msg = Rollback::apply($log);

        return str_starts_with($msg, '✓') ? $this->json(['ok' => true, 'log_id' => $log->id]) : Response::error($msg);
    }

    public function schema(JsonSchema $schema): array
    {
        return ['log_id' => $schema->integer()->description('id from get_activity.')->required()];
    }
}
