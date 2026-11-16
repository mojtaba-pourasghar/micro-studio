<?php

namespace App\Mcp\Tools;

use App\Models\Setting;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Change page texts / settings. Pass "values" as {"group.field": "new value"} using keys from get_settings. An empty string or null resets a key to its default.')]
class UpdateSettings extends AgentTool
{
    public function handle(Request $request): Response
    {
        if ($denied = $this->deny('content.write')) {
            return $denied;
        }
        $values = $request->get('values');
        if (! is_array($values) || ! $values) {
            return Response::error('"values" must be an object of "group.field" => value.');
        }
        $schema = Setting::schema();
        $unknown = array_diff(array_keys($values), array_keys($schema));
        if ($unknown) {
            return Response::error('Unknown keys: '.implode(', ', $unknown).'. Use get_settings to see valid keys.');
        }
        $done = [];
        foreach ($values as $key => $value) {
            if (! is_null($value) && ! is_scalar($value)) {
                return Response::error("$key: value must be text.");
            }
            $value = $value === null ? '' : trim((string) $value);
            if (mb_strlen($value) > 2000) {
                return Response::error("$key: max 2000 characters.");
            }
            if ($value === '' || $value === $schema[$key]['default']) {
                Setting::where('key', $key)->get()->each->delete();
                $done[$key] = 'reset to default';
            } else {
                Setting::put($key, $value);
                $done[$key] = 'updated';
            }
        }

        return $this->json(['ok' => true, 'changed' => $done]);
    }

    public function schema(JsonSchema $schema): array
    {
        return ['values' => $schema->object()->description('{"home.hero_lead": "…", "contact.phone_display": "…"}')->required()];
    }
}
