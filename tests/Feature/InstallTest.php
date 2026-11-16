<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Installer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstallTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        @unlink(Installer::lockPath());
        parent::tearDown();
    }

    public function test_install_command_creates_admin_and_can_reset_password(): void
    {
        $this->artisan('ms:install', ['--email' => 'owner@example.com', '--password' => 'first-password'])->assertSuccessful();
        $this->assertTrue(Hash::check('first-password', User::where('email', 'owner@example.com')->value('password')));

        $this->artisan('ms:install', ['--email' => 'owner@example.com', '--password' => 'second-password'])->assertSuccessful();
        $this->assertSame(1, User::count());
        $this->assertTrue(Hash::check('second-password', User::first()->password));
        $this->assertArrayNotHasKey('password', User::first()->toArray());
    }

    public function test_web_installer_needs_the_key_and_runs_once(): void
    {
        config(['app.install_key' => 'secret-key']);
        $this->get('/install')->assertNotFound();
        $this->get('/install?key=secret-key')->assertOk();
        $this->post('/install', ['key' => 'secret-key', 'name' => 'مدیر', 'email' => 'web@example.com', 'password' => 'web-password-1', 'password_confirmation' => 'web-password-1'])
            ->assertOk()->assertSee('/mcp/ms_', false);
        $this->assertDatabaseHas('users', ['email' => 'web@example.com']);
        $this->get('/install?key=secret-key')->assertNotFound();
    }
}
