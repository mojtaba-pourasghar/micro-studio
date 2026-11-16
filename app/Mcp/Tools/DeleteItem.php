<?php

namespace App\Mcp\Tools;

use App\Mcp\ContentTypes;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Description('Delete an item. Games, services, team, testimonials, faqs, blocks and posts are soft-deleted and can be restored with rollback_change. Requires the "delete" ability; published posts cannot be deleted without posts.publish.')]
#[IsDestructive]
class DeleteItem extends AgentTool
{
    public function handle(Request $request): Response
    {
        if ($denied = $this->deny('delete')) {
            return $denied;
        }
        $type = (string) $request->get('type');
        $def = ContentTypes::get($type);
        if (! $def || ! $def['write'] || $type === 'media') {
            return Response::error('This type cannot be deleted by the agent.');
        }
        $m = GetItem::find($type, $request->get('id'), false);
        if (! $m) {
            return Response::error('Item not found.');
        }
        if ($type === 'posts' && $m->status === 'published' && ($denied = $this->deny('posts.publish'))) {
            return $denied;
        }
        $m->delete();
        $log = \App\Models\ActivityLog::where('subject_type', $m->getMorphClass())->where('subject_id', $m->getKey())->where('action', 'deleted')->latest('id')->first();

        return $this->json(['ok' => true, 'deleted' => ContentTypes::summary($type, $m), 'rollback_log_id' => $log?->id]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->enum(array_keys(ContentTypes::all()))->required(),
            'id' => $schema->string()->description('Numeric id or slug.')->required(),
        ];
    }
}
