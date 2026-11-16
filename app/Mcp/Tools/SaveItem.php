<?php

namespace App\Mcp\Tools;

use App\Mcp\ContentTypes;
use App\Models\Post;
use App\Support\Actor;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Validator;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create an item (omit id) or update an existing one (give id or slug; only the keys you send are changed). Every change is logged and can be rolled back. Blog posts: you can save as "draft" or send for review with status "pending_review"; publishing needs the owner\'s approval unless the token has posts.publish, and published posts cannot be changed without it.')]
class SaveItem extends AgentTool
{
    protected const READ_ONLY = ['id', 'created_at', 'updated_at', 'deleted_at', 'review_note', 'created_by', 'path', 'source', 'mime', 'size', 'width', 'height', 'original_name', 'read_minutes'];

    public function handle(Request $request): Response
    {
        $type = (string) $request->get('type');
        $def = ContentTypes::get($type);
        if (! $def || ! $def['write']) {
            return Response::error('Unknown or read-only type.');
        }
        if ($denied = $this->deny($def['ability'])) {
            return $denied;
        }
        $data = $request->get('data');
        if (! is_array($data) || ! $data) {
            return Response::error('"data" must be a non-empty object of field => value.');
        }
        $data = array_intersect_key($data, $def['fields']);
        $data = array_diff_key($data, array_flip(self::READ_ONLY));
        if (! $data) {
            return Response::error('No writable fields given. Writable: '.implode(', ', array_diff(array_keys($def['fields']), self::READ_ONLY)));
        }

        $id = $request->get('id');
        $record = $id !== null && $id !== '' ? GetItem::find($type, $id) : null;
        if (($id !== null && $id !== '') && ! $record) {
            return Response::error('Item not found; omit id to create a new one.');
        }
        if ($type === 'media' && ! $record) {
            return Response::error('Create media with upload_image.');
        }

        if ($type === 'posts' && ($error = $this->postGuard($record, $data))) {
            return Response::error($error);
        }

        $rules = array_intersect_key(ContentTypes::rules($type, $record), $record ? $this->ruleKeysFor($data, ContentTypes::rules($type, $record)) : ContentTypes::rules($type, $record));
        $v = Validator::make($data, $rules);
        if ($v->fails()) {
            return Response::error("Validation failed:\n".implode("\n", $v->errors()->all()));
        }

        if ($record) {
            if (method_exists($record, 'trashed') && $record->trashed()) {
                return Response::error('Item is deleted; restore it first with rollback_change or ask the owner.');
            }
            $record->fill($data)->save();
            $action = 'updated';
        } else {
            if ($type === 'posts') {
                $data += ['status' => 'draft'];
                $data['created_by'] = 'agent';
            }
            $record = $def['model']::create($data);
            $action = 'created';
        }

        return $this->json([
            'ok' => true, 'action' => $action, 'type' => $type, 'id' => $record->getKey(),
            'url' => ContentTypes::publicUrl($type, $record), 'item' => ContentTypes::summary($type, $record->fresh() ?? $record),
            'note' => $type === 'posts' && ($record->status ?? null) !== 'published' ? 'Drafts are visible only to logged-in admins. Use status "pending_review" to ask the owner to publish.' : null,
        ]);
    }

    /** Rules for the sent keys plus their nested "key.*" rules. */
    protected function ruleKeysFor(array $data, array $rules): array
    {
        $keys = [];
        foreach (array_keys($rules) as $r) {
            if (array_key_exists(explode('.', $r)[0], $data)) {
                $keys[$r] = true;
            }
        }

        return $keys;
    }

    protected function postGuard(?Post $post, array &$data): ?string
    {
        $canPublish = (bool) Actor::agent()?->allows('posts.publish');
        if (! $canPublish && ($data['status'] ?? null) === 'published') {
            return 'This token cannot publish. Save with status "pending_review"; the owner approves it in the admin.';
        }
        if (! $canPublish && $post && $post->status === 'published') {
            return 'This post is already published; only the owner can change it. Create a new draft instead, or ask the owner.';
        }
        if (! $canPublish && ! empty($data['is_featured'])) {
            return 'Only the owner can feature a post.';
        }
        if ($post && ($data['status'] ?? null) === 'pending_review') {
            $post->review_note = null;
        }

        return null;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->enum(array_keys(array_filter(ContentTypes::all(), fn ($d) => $d['write'])))->required(),
            'id' => $schema->string()->description('Omit to create. Numeric id, or slug for games/services/posts/post_categories.'),
            'data' => $schema->object()->description('Field => value. See describe_content_types for every field and format.')->required(),
        ];
    }
}
