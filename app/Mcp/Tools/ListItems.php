<?php

namespace App\Mcp\Tools;

use App\Mcp\ContentTypes;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('List items of a content type (compact rows: id, title, slug, status…). Supports search, a filter on status/group/page/category, and pagination.')]
#[IsReadOnly]
class ListItems extends AgentTool
{
    public function handle(Request $request): Response
    {
        $type = (string) $request->get('type');
        $def = ContentTypes::get($type);
        if (! $def) {
            return Response::error('Unknown type. Valid: '.implode(', ', array_keys(ContentTypes::all())));
        }
        if ($type === 'contact_messages' && ($denied = $this->deny('messages.read'))) {
            return $denied;
        }
        $model = $def['model'];
        $q = $model::query();
        if ($request->get('include_deleted') && in_array(SoftDeletes::class, class_uses_recursive($model), true)) {
            $q->withTrashed();
        }
        if ($s = trim((string) $request->get('search'))) {
            $q->where(fn ($w) => collect($def['search'])->each(fn ($c) => $w->orWhere($c, 'like', "%$s%")));
        }
        $table = (new $model)->getTable();
        foreach (['status', 'group', 'page', 'category'] as $f) {
            if (($v = $request->get($f)) !== null && \Illuminate\Support\Facades\Schema::hasColumn($table, $f)) {
                $q->where($f, $v);
            }
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'sort_order')) {
            $q->orderBy('sort_order');
        }
        $q->orderByDesc('id');
        $perPage = min(100, max(1, (int) ($request->get('per_page') ?: 30)));
        $page = $q->paginate($perPage, ['*'], 'page', max(1, (int) ($request->get('page') ?: 1)));

        return $this->json([
            'type' => $type, 'total' => $page->total(), 'page' => $page->currentPage(), 'last_page' => $page->lastPage(),
            'items' => collect($page->items())->map(fn ($m) => ContentTypes::summary($type, $m))->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->enum(array_keys(ContentTypes::all()))->description('Content type.')->required(),
            'search' => $schema->string()->description('Text to search in titles and texts.'),
            'status' => $schema->string()->description('posts: draft|pending_review|published|rejected; games: released|in_development|designing; content_plans: planned|in_progress|done|skipped'),
            'group' => $schema->string()->description('blocks only: honors|promises|values|steps|tools|support|contact_steps'),
            'page' => $schema->string()->description('faqs only: services|contact'),
            'category' => $schema->string()->description('games only: game|edu'),
            'include_deleted' => $schema->boolean()->description('Include soft-deleted items.'),
            'per_page' => $schema->integer()->description('Default 30, max 100.'),
        ];
    }
}
