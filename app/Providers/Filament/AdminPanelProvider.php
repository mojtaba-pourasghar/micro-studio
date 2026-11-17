<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Auth\Login::class)
            ->brandName('پنل میکرو استودیو')
            ->favicon(asset('favicon.svg'))
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->brandLogoHeight('2.6rem')
            ->colors(['primary' => Color::hex('#7457ff'), 'gray' => Color::Slate])
            ->font('Vazirmatn', url: asset('assets/css/admin-font.css'), provider: \Filament\FontProviders\LocalFontProvider::class)
            ->sidebarCollapsibleOnDesktop()
            ->defaultAvatarProvider(\App\Filament\LocalAvatarProvider::class)
            ->navigationGroups(['محتوای سایت', 'وبلاگ', 'ایجنت هوش مصنوعی', 'رسانه', 'ارتباطات', 'سیستم'])
            ->navigationItems([
                NavigationItem::make('مشاهده‌ی سایت')->url('/', shouldOpenInNewTab: true)->icon(Heroicon::OutlinedGlobeAlt)->sort(100),
            ])
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('filament.admin-head'))
            ->renderHook(PanelsRenderHook::BODY_END, fn () => auth()->check() ? view('filament.session-guard') : '')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
