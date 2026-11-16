<?php

namespace App\Mcp\Tools;

use App\Mcp\ContentTypes;
use App\Support\Actor;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Lists the content types (games, services, team_members, testimonials, faqs, blocks, post_categories, posts, content_plans, media, contact_messages) with every field and its format. Pass a type to see only that one.')]
#[IsReadOnly]
class DescribeContentTypes extends AgentTool
{
    public function handle(Request $request): Response
    {
        $only = $request->get('type');
        $out = [];
        foreach (ContentTypes::all() as $type => $def) {
            if ($only && $only !== $type) {
                continue;
            }
            $out[$type] = [
                'label' => $def['label'], 'writable' => $def['write'], 'needs_ability' => $def['ability'],
                'allowed_for_this_token' => $def['ability'] ? (bool) Actor::agent()?->allows($def['ability']) : true,
                'fields' => $def['fields'],
            ];
        }

        return $this->json([
            'types' => $out,
            'conventions' => [
                'images' => 'Image fields hold a public path. Upload new images with upload_image and use the returned "path".',
                'headings' => ContentTypes::HEADING,
                'digits' => 'Write Persian text with Persian digits (۱۲۳); numeric fields such as stats.value use latin digits.',
                'partial_updates' => 'save_item with an id only changes the keys you send.',
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return ['type' => $schema->string()->description('Optional: one content type name.')];
    }
}
