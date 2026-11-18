<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteUpdate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_run_updates_from_the_panel(): void
    {
        $this->get('/admin/site-update')->assertRedirect();
        $this->actingAs(User::factory()->create());
        $this->get('/admin/site-update')->assertOk()->assertSee('سایت به‌روز است');
        Livewire::test(SiteUpdate::class)->callAction('run')->assertHasNoErrors()->assertSet('log', fn ($log) => count($log) === 2);
    }
}
