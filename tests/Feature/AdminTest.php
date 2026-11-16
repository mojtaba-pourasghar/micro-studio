<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentSeeder::class);
        $this->admin = User::factory()->create();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_every_admin_page_loads(): void
    {
        $this->actingAs($this->admin);
        foreach (['', '/settings', '/games', '/games/create', '/games/razino/edit', '/services', '/posts', '/posts/create', '/posts/razino-new-chapters/edit',
            '/post-categories', '/team-members', '/testimonials', '/faqs', '/blocks', '/content-rules', '/content-plans', '/agent-tokens',
            '/activity-logs', '/contact-messages', '/media', '/users'] as $path) {
            $this->get('/admin'.$path)->assertOk();
        }
    }

    public function test_settings_page_saves_values(): void
    {
        $this->actingAs($this->admin);
        Livewire::test(SiteSettings::class)
            ->set('data.home.hero_lead', 'متن از پنل')
            ->call('save');
        $this->assertSame('متن از پنل', Setting::get('home.hero_lead'));
        $this->assertSame(1, Setting::count());
    }

    public function test_owner_can_publish_a_pending_post(): void
    {
        $this->actingAs($this->admin);
        $post = Post::create(['slug' => 'pending', 'title' => 'منتظر', 'excerpt' => 'e', 'status' => 'pending_review', 'created_by' => 'agent']);
        $this->get('/blog/pending')->assertOk(); // admins can preview
        Livewire::test(EditPost::class, ['record' => $post->slug])->callAction('publish');
        $this->assertSame('published', $post->fresh()->status);
        $this->assertNotNull($post->fresh()->published_at);
        $this->assertDatabaseHas('activity_logs', ['subject_id' => $post->id, 'actor' => 'admin:'.$this->admin->email, 'action' => 'updated']);
    }
}
