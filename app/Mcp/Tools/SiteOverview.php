<?php

namespace App\Mcp\Tools;

use App\Mcp\ContentTypes;
use App\Models\ContactMessage;
use App\Models\ContentPlan;
use App\Models\ContentRule;
use App\Models\Post;
use App\Support\Actor;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Start here. Returns the site URL, item counts per content type, work waiting for the agent (due content-calendar items, posts the owner returned with feedback), items waiting for the owner, and this token\'s abilities.')]
#[IsReadOnly]
class SiteOverview extends AgentTool
{
    public function handle(Request $request): Response
    {
        $counts = [];
        foreach (ContentTypes::all() as $type => $def) {
            $counts[$type] = $def['model']::query()->count();
        }
        $token = Actor::agent();

        return $this->json([
            'site' => ['name' => setting('site.name'), 'url' => url('/'), 'admin' => url('/admin'), 'today' => today()->toDateString()],
            'token' => ['name' => $token?->name, 'abilities' => $token?->abilities ?? 'all except posts.publish, rules.approve, messages.read'],
            'approved_rules' => ContentRule::where('status', 'approved')->count(),
            'counts' => $counts,
            'agent_todo' => [
                'due_plans' => ContentPlan::whereIn('status', ['planned', 'in_progress'])->where(fn ($q) => $q->whereNull('due_date')->orWhereDate('due_date', '<=', today()))
                    ->orderBy('due_date')->limit(20)->get(['id', 'title', 'type', 'due_date', 'status', 'game_id', 'post_category_id'])
                    ->map(fn ($p) => $p->toArray() + ['due_date' => optional($p->due_date)->toDateString()]),
                'returned_posts' => Post::where('status', 'rejected')->get(['id', 'title', 'slug', 'review_note']),
            ],
            'waiting_for_owner' => [
                'posts_pending_review' => Post::where('status', 'pending_review')->count(),
                'proposed_rules' => ContentRule::where('status', 'proposed')->count(),
                'unread_messages' => ContactMessage::whereNull('read_at')->count(),
            ],
            'next_steps' => 'Call get_content_rules before writing anything. Use describe_content_types for field formats.',
        ]);
    }
}
