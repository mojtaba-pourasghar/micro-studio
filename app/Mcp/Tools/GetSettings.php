<?php

namespace App\Mcp\Tools;

use App\Models\Setting;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Read the editable page texts and site settings (hero titles and leads, section headings, contact info, social links, page images). Groups: site, contact, home, games, services, about, contact_page, blog.')]
#[IsReadOnly]
class GetSettings extends AgentTool
{
    public function handle(Request $request): Response
    {
        $group = $request->get('group');
        $out = [];
        foreach (Setting::schema() as $key => $f) {
            if ($group && ! str_starts_with($key, $group.'.')) {
                continue;
            }
            $value = Setting::get($key);
            $out[$key] = ['label' => $f['label'], 'type' => $f['type'], 'value' => $value, 'is_default' => $value === $f['default']];
        }

        return $this->json(['settings' => $out, 'heading_syntax' => \App\Mcp\ContentTypes::HEADING, 'image_type' => \App\Mcp\ContentTypes::IMAGE]);
    }

    public function schema(JsonSchema $schema): array
    {
        return ['group' => $schema->string()->enum(array_keys(config('site.groups')))->description('Optional group filter.')];
    }
}
