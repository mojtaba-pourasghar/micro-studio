<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AgentToken;
use App\Models\ContentRule;
use App\Models\Game;
use App\Models\Post;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AgentMcpTest extends TestCase
{
    use RefreshDatabase;

    protected string $plain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentSeeder::class);
        [, $this->plain] = AgentToken::issue('test', null);
    }

    protected function call_tool(string $name, array $args = [], ?string $token = null): array
    {
        $res = $this->postJson('/mcp/'.($token ?? $this->plain), [
            'jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/call', 'params' => ['name' => $name, 'arguments' => $args],
        ], ['Accept' => 'application/json, text/event-stream', 'MCP-Protocol-Version' => '2025-06-18']);
        $res->assertOk();
        $result = $res->json('result');

        return ['error' => (bool) ($result['isError'] ?? false), 'text' => $result['content'][0]['text'] ?? '', 'data' => json_decode($result['content'][0]['text'] ?? 'null', true)];
    }

    protected function tearDown(): void
    {
        @array_map('unlink', glob(public_path('uploads/*/*/test-*')) ?: []);
        parent::tearDown();
    }

    public function test_invalid_or_revoked_token_is_rejected(): void
    {
        $this->postJson('/mcp/ms_wrong', ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list'])->assertUnauthorized();
        AgentToken::query()->update(['revoked_at' => now()]);
        $this->postJson('/mcp/'.$this->plain, ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list'])->assertUnauthorized();
    }

    public function test_bearer_header_works_and_lists_tools(): void
    {
        $res = $this->postJson('/mcp', ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list'],
            ['Authorization' => 'Bearer '.$this->plain, 'Accept' => 'application/json, text/event-stream', 'MCP-Protocol-Version' => '2025-06-18']);
        $res->assertOk();
        $names = collect($res->json('result.tools'))->pluck('name');
        foreach (['site_overview', 'get_content_rules', 'save_item', 'upload_image', 'update_settings', 'rollback_change'] as $tool) {
            $this->assertContains($tool, $names);
        }
    }

    public function test_overview_and_rules(): void
    {
        ContentRule::query()->first()->update(['status' => 'approved']);
        $this->assertSame(9, $this->call_tool('site_overview')['data']['counts']['games']);
        $rules = $this->call_tool('get_content_rules')['data'];
        $this->assertCount(1, $rules['approved']);
        $this->assertFalse($this->call_tool('propose_content_rule', ['title' => 'قانون', 'body' => 'متن'])['error']);
        $this->assertSame('agent', ContentRule::latest('id')->value('proposed_by'));
    }

    public function test_agent_can_draft_and_submit_but_not_publish(): void
    {
        $r = $this->call_tool('save_item', ['type' => 'posts', 'data' => ['slug' => 'agent-post', 'title' => 'مطلب', 'excerpt' => 'خلاصه', 'body' => '<p>متن</p>', 'status' => 'pending_review']]);
        $this->assertFalse($r['error'], $r['text']);
        $post = Post::where('slug', 'agent-post')->first();
        $this->assertSame(['pending_review', 'agent'], [$post->status, $post->created_by]);

        $this->assertTrue($this->call_tool('save_item', ['type' => 'posts', 'id' => 'agent-post', 'data' => ['status' => 'published']])['error']);
        $this->assertTrue($this->call_tool('save_item', ['type' => 'posts', 'id' => 'razino-new-chapters', 'data' => ['title' => 'x']])['error']);
        $this->assertSame('pending_review', $post->fresh()->status);
    }

    public function test_publish_ability_allows_publishing(): void
    {
        [, $plain] = AgentToken::issue('publisher', ['posts.write', 'posts.publish']);
        $r = $this->call_tool('save_item', ['type' => 'posts', 'data' => ['slug' => 'live', 'title' => 'زنده', 'excerpt' => 'e', 'status' => 'published']], $plain);
        $this->assertFalse($r['error'], $r['text']);
        $this->get('/blog/live')->assertOk();
    }

    public function test_partial_update_validation_and_rollback(): void
    {
        $r = $this->call_tool('save_item', ['type' => 'games', 'id' => 'time-guardians', 'data' => ['age_range' => '۵ تا ۱۰ سال']]);
        $this->assertFalse($r['error'], $r['text']);
        $this->assertSame('۵ تا ۱۰ سال', Game::where('slug', 'time-guardians')->value('age_range'));

        $this->assertTrue($this->call_tool('save_item', ['type' => 'games', 'id' => 'time-guardians', 'data' => ['category' => 'bogus']])['error']);

        $log = ActivityLog::where('actor', 'agent:test')->latest('id')->first();
        $this->assertFalse($this->call_tool('rollback_change', ['log_id' => $log->id])['error']);
        $this->assertSame('۵ تا ۹ سال', Game::where('slug', 'time-guardians')->value('age_range'));
    }

    public function test_abilities_are_enforced(): void
    {
        [, $readOnly] = AgentToken::issue('reader', []);
        $this->assertTrue($this->call_tool('save_item', ['type' => 'faqs', 'data' => ['page' => 'contact', 'question' => 'q', 'answer' => 'a']], $readOnly)['error']);
        $this->assertTrue($this->call_tool('list_items', ['type' => 'contact_messages'])['error']); // messages.read is restricted by default
        $this->assertTrue($this->call_tool('delete_item', ['type' => 'posts', 'id' => 'razino-new-chapters'])['error']); // published post
    }

    public function test_settings_round_trip(): void
    {
        $this->assertFalse($this->call_tool('update_settings', ['values' => ['contact.phone_display' => '+98 21 1111 2222']])['error']);
        $this->get('/contact')->assertSee('+98 21 1111 2222');
        $this->assertTrue($this->call_tool('update_settings', ['values' => ['nope.key' => 'x']])['error']);
        $this->assertFalse($this->call_tool('update_settings', ['values' => ['contact.phone_display' => '']])['error']);
        $this->get('/contact')->assertSee('0912 891 5975');
    }

    public function test_image_upload_via_mcp_and_rest(): void
    {
        $png = base64_encode(file_get_contents(public_path('assets/img/characters/razino-dino.webp')));
        $r = $this->call_tool('upload_image', ['filename' => 'test-dino.webp', 'alt' => 'دایناسور', 'base64' => $png]);
        $this->assertFalse($r['error'], $r['text']);
        $this->assertFileExists(public_path($r['data']['path']));

        $this->assertTrue($this->call_tool('upload_image', ['filename' => 'test-x.txt', 'alt' => 'x', 'base64' => base64_encode('not an image')])['error']);

        $file = UploadedFile::fake()->createWithContent('test-girl.webp', file_get_contents(public_path('assets/img/characters/razino-girl.webp')));
        $this->post('/api/agent/media', ['file' => $file, 'alt' => 'دختر'], ['Authorization' => 'Bearer '.$this->plain, 'Accept' => 'application/json'])
            ->assertCreated()->assertJsonStructure(['path', 'url']);
    }
}
