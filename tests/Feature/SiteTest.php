<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Setting;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentSeeder::class);
    }

    public static function pages(): array
    {
        return [['/'], ['/games'], ['/games/razino'], ['/games/hoho-math-3'], ['/services'], ['/about'], ['/contact'], ['/blog'], ['/blog?cat=art'], ['/blog?q=رازینو'], ['/blog/razino-new-chapters'], ['/sitemap.xml']];
    }

    #[DataProvider('pages')]
    public function test_public_pages_render(string $url): void
    {
        $this->get($url)->assertOk();
    }

    public function test_home_shows_database_content_and_settings(): void
    {
        Setting::put('home.hero_lead', 'متن تازه‌ی Hero');
        $this->get('/')->assertSee('متن تازه‌ی Hero')->assertSee('دو آسمان: کایرو و ایلارا')->assertSee('همه‌ی حقوق محفوظ است.');
    }

    public function test_heading_syntax_is_rendered_and_escaped(): void
    {
        $this->assertSame('a <span class="outline">b</span><br><span class="grad">c</span> &lt;x&gt;', (string) heading('a [[b]]|{{c}} <x>'));
    }

    public function test_drafts_are_hidden_from_visitors(): void
    {
        Post::create(['slug' => 'secret-draft', 'title' => 'پیش‌نویس', 'excerpt' => 'x', 'status' => 'pending_review']);
        $this->get('/blog/secret-draft')->assertNotFound();
        $this->get('/blog')->assertDontSee('پیش‌نویس');
    }

    public function test_contact_form_stores_message(): void
    {
        \Illuminate\Support\Facades\Http::fake();
        $this->withSession(['captcha.contact' => ['code' => '12345', 'at' => time()]])->postJson('/contact', ['name' => 'علی', 'email' => 'ali@example.com', 'message' => 'سلام، یک پروژه داریم.', 'newsletter' => '1', 'captcha' => '12345'])
            ->assertOk()->assertJson(['ok' => true]);
        $this->assertDatabaseHas('contact_messages', ['email' => 'ali@example.com']);
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'ali@example.com']);
    }

    public function test_contact_form_validates_and_blocks_honeypot(): void
    {
        $this->postJson('/contact', ['name' => '', 'email' => 'bad', 'message' => 'short'])->assertStatus(422)->assertJsonValidationErrors(['name', 'email', 'message', 'captcha']);
        $this->postJson('/contact', ['name' => 'bot', 'email' => 'b@b.com', 'message' => 'spam spam spam', 'website' => 'x'])->assertOk();
        $this->assertSame(0, ContactMessage::count());
    }

    public function test_post_body_is_sanitized(): void
    {
        $post = Post::create(['slug' => 's', 'title' => 't', 'excerpt' => 'e', 'body' => '<p onclick="x()">a</p><script>alert(1)</script><a href="javascript:x">l</a><img src="assets/x.webp">']);
        $this->assertSame('<p>a</p><a>l</a><img src="assets/x.webp">', $post->body);
        $this->assertStringContainsString('src="'.asset('assets/x.webp').'"', $post->renderedBody());
    }

    public function test_jalali_conversion_round_trip(): void
    {
        $this->assertSame([1405, 8, 19], \App\Support\Jalali::fromGregorian(2026, 11, 10));
        $this->assertSame('2026-11-10', \App\Support\Jalali::toGregorian(1405, 8, 19));
        $this->assertSame('2025-03-20', \App\Support\Jalali::toGregorian(1403, 12, 30));
    }
}
