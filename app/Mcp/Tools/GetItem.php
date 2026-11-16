<?php

namespace App\Mcp\Tools;

use App\Mcp\ContentTypes;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Get every field of one item, by id or slug.')]
#[IsReadOnly]
class GetItem extends AgentTool
{
    public static function find(string $type, mixed $idOrSlug, bool $withTrashed = true): ?Model
    {
        $def = ContentTypes::get($type);
        if (! $def || $idOrSlug === null || $idOrSlug === '') {
            return null;
        }
        $model = $def['model'];
        $q = $withTrashed && in_array(SoftDeletes::class, class_uses_recursive($model), true) ? $model::withTrashed() : $model::query();
        if (is_numeric($idOrSlug)) {
            return $q->find((int) $idOrSlug);
        }

        return \Illuminate\Support\Facades\Schema::hasColumn((new $model)->getTable(), 'slug') ? $q->where('slug', $idOrSlug)->first() : null;
    }

    public function handle(Request $request): Response
    {
        $type = (string) $request->get('type');
        if ($type === 'contact_messages' && ($denied = $this->deny('messages.read'))) {
            return $denied;
        }
        $m = static::find($type, $request->get('id'));
        if (! $m) {
            return Response::error('Item not found.');
        }

        return $this->json(['type' => $type, 'url' => ContentTypes::publicUrl($type, $m), 'item' => $m->toArray()]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->enum(array_keys(ContentTypes::all()))->required(),
            'id' => $schema->string()->description('Numeric id, or slug for games, services, posts and post_categories.')->required(),
        ];
    }
}
