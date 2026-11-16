<?php

namespace App\Mcp\Tools;

use App\Models\ContentRule;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Propose a new content rule (or a change to one). It stays "proposed" until the site owner approves it in the admin.')]
class ProposeContentRule extends AgentTool
{
    public function handle(Request $request): Response
    {
        if ($denied = $this->deny('rules.propose')) {
            return $denied;
        }
        $data = $request->validate(['title' => ['required', 'string', 'max:120'], 'body' => ['required', 'string', 'max:3000']]);
        $rule = ContentRule::create($data + ['status' => 'proposed', 'proposed_by' => 'agent', 'sort_order' => (int) ContentRule::max('sort_order') + 1]);

        return $this->json(['ok' => true, 'id' => $rule->id, 'status' => 'proposed', 'note' => 'Waiting for the owner\'s approval.']);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->description('Short rule title (Persian).')->required(),
            'body' => $schema->string()->description('The rule text (Persian), concrete and checkable.')->required(),
        ];
    }
}
