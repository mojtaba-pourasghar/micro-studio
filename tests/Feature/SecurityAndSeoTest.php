<?php

namespace Tests\Feature;

use App\Filament\Auth\Login;
use App\Models\ContactMessage;
use App\Models\User;
use App\Support\Captcha;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityAndSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentSeeder::class);
        config(['services.smsir.api_key' => 'test-key', 'services.smsir.line_number' => '3000123', 'services.smsir.base_url' => 'https://api.sms.ir/v1']);
        Http::fake(['api.sms.ir/*' => Http::response(['status' => 1, 'message' => 'موفق'], 200)]);
    }

    protected function captcha(string $key = 'contact', string $code = '12345'): array
    {
        return ["captcha.$key" => ['code' => $code, 'at' => time()]];
    }

    public function test_captcha_image_is_served(): void
    {
        $this->get('/captcha/contact.png')->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->get('/captcha/other.png')->assertNotFound();
    }

    public function test_contact_requires_a_correct_captcha_and_works_only_once(): void
    {
        $data = ['name' => 'علی', 'email' => 'ali@example.com', 'message' => 'سلام، یک پروژه داریم.'];
        $this->withSession($this->captcha())->postJson('/contact', $data + ['captcha' => '99999'])->assertStatus(422)->assertJsonValidationErrors('captcha');
        $this->withSession($this->captcha())->postJson('/contact', $data + ['captcha' => '۱۲۳۴۵'])->assertOk(); // Persian digits accepted
        $this->postJson('/contact', $data + ['captcha' => '12345'])->assertStatus(422); // code is single-use
        $this->assertSame(1, ContactMessage::count());
    }

    public function test_contact_message_sends_sms_to_the_owner(): void
    {
        $this->withSession($this->captcha())->postJson('/contact', ['name' => 'سارا', 'phone' => '09120000000', 'email' => 's@example.com', 'message' => 'درخواست ساخت اپ آموزشی', 'captcha' => '12345'])->assertOk();
        Http::assertSent(fn ($req) => str_ends_with($req->url(), '/send/bulk')
            && $req->header('x-api-key')[0] === 'test-key'
            && $req['mobiles'] === ['09128915975']
            && str_contains($req['messageText'], 'سارا'));
    }

    public function test_newsletter_sends_sms_once_and_blocks_honeypot(): void
    {
        $this->postJson('/newsletter', ['email' => 'new@example.com'])->assertOk();
        $this->postJson('/newsletter', ['email' => 'new@example.com'])->assertOk();
        $this->postJson('/newsletter', ['email' => 'bot@example.com', 'website' => 'x'])->assertOk();
        Http::assertSentCount(1);
        $this->assertDatabaseMissing('newsletter_subscribers', ['email' => 'bot@example.com']);
    }

    public function test_sms_is_skipped_without_credentials(): void
    {
        config(['services.smsir.api_key' => null]);
        $this->postJson('/newsletter', ['email' => 'quiet@example.com'])->assertOk();
        Http::assertNothingSent();
    }

    public function test_admin_login_requires_captcha(): void
    {
        $user = User::factory()->create(['email' => 'owner@example.com', 'password' => 'secret-password']);
        Livewire::test(Login::class)
            ->set('data.email', 'owner@example.com')->set('data.password', 'secret-password')->set('data.captcha', '00000')
            ->call('authenticate')->assertHasErrors(['data.captcha']);
        $this->assertGuest();

        session($this->captcha('login', '54321'));
        Livewire::test(Login::class)
            ->set('data.email', 'owner@example.com')->set('data.password', 'secret-password')->set('data.captcha', '54321')
            ->call('authenticate')->assertHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_session_alive_endpoint(): void
    {
        $this->getJson('/session/alive')->assertJson(['auth' => false]);
        $this->actingAs(User::factory()->create())->getJson('/session/alive')->assertJson(['auth' => true]);
    }

    public function test_game_page_has_media_structured_data_and_no_source_link(): void
    {
        $html = $this->get('/games/pashmak')->assertOk()->assertSee('gameplay', false)->assertSee('video-card', false)->assertSee('data-lightbox', false)->getContent();
        $this->assertStringNotContainsString('github.com', $html);
        preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $html, $m);
        $types = collect(json_decode($m[1], true)['@graph'])->pluck('@type')->flatten()->all();
        foreach (['Organization', 'WebSite', 'BreadcrumbList', 'MobileApplication', 'VideoObject'] as $t) {
            $this->assertContains($t, $types);
        }
    }

    public function test_seo_endpoints(): void
    {
        $sitemap = $this->get('/sitemap.xml')->assertOk()->getContent();
        foreach (['/games/pashmak', '/services', '/about', '/contact', '/blog?cat=our-work', '/blog/razino-new-chapters'] as $path) {
            $this->assertStringContainsString(htmlspecialchars(url($path), ENT_XML1), $sitemap);
        }
        $this->assertStringContainsString('<video:video>', $sitemap);
        $this->get('/feed.xml')->assertOk()->assertSee('<rss', false);
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.url('/sitemap.xml'));
        $this->get('/manifest.webmanifest')->assertOk()->assertJsonPath('dir', 'rtl');
        $this->get('/llms.txt')->assertOk()->assertSee('دنیای بازی پشمک');
        $this->get('/blog?q=test')->assertSee('noindex', false);
    }

    public function test_contact_details_and_designer_credit(): void
    {
        $this->get('/contact')->assertSee('mojtaba.developer@gmail.com')->assertSee('https://wa.me/989128915975', false)
            ->assertSee('https://www.instagram.com/micro_studio.ir', false)->assertSee('https://mojtabapourasghar.ir', false);
    }
}
