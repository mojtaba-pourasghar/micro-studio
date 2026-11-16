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
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
            ->login()
            ->brandName('پنل میکرو استودیو')
            ->favicon(asset('favicon.svg'))
            ->colors(['primary' => Color::hex('#7457ff'), 'gray' => Color::Slate])
            ->font('Vazirmatn', provider: null)
            ->sidebarCollapsibleOnDesktop()
            ->defaultAvatarProvider(\App\Filament\LocalAvatarProvider::class)
            ->navigationGroups(['محتوای سایت', 'وبلاگ', 'ایجنت هوش مصنوعی', 'رسانه', 'ارتباطات', 'سیستم'])
            ->navigationItems([
                NavigationItem::make('مشاهده‌ی سایت')->url('/', shouldOpenInNewTab: true)->icon(Heroicon::OutlinedGlobeAlt)->sort(100),
            ])
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => new HtmlString(
                '<style>@font-face{font-family:"Vazirmatn";src:url("'.asset('assets/fonts/Vazirmatn-var.woff2').'") format("woff2");font-weight:100 900;font-display:swap}</style>'
            ))
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
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
