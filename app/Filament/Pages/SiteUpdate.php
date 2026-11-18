<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use UnitEnum;

/**
 * Runs database updates from the admin, for hosts without SSH/Terminal (cPanel):
 * after extracting a new package, the admin presses one button instead of `php artisan migrate --force`.
 */
class SiteUpdate extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static string|UnitEnum|null $navigationGroup = 'سیستم';

    protected static ?int $navigationSort = 90;

    protected static ?string $navigationLabel = 'به‌روزرسانی سایت';

    protected static ?string $title = 'به‌روزرسانی سایت';

    protected static ?string $slug = 'site-update';

    protected string $view = 'filament.pages.site-update';

    public array $log = [];

    /** @return array<int, string> migration names not run yet */
    public function pending(): array
    {
        $ran = DB::table('migrations')->pluck('migration')->all();
        $files = collect(glob(database_path('migrations/*.php')) ?: [])->map(fn ($f) => basename($f, '.php'));

        return $files->reject(fn ($m) => in_array($m, $ran, true))->values()->all();
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $n = count((new static)->pending());
        } catch (\Throwable) {
            return null;
        }

        return $n ? (string) $n : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run')
                ->label('اجرای به‌روزرسانی')
                ->icon(Heroicon::OutlinedArrowPath)
                ->requiresConfirmation()
                ->modalDescription('تغییرات تازه‌ی دیتابیس اجرا و کش سایت پاک می‌شود. پیش از آن از دیتابیس نسخه‌ی پشتیبان بگیرید.')
                ->action(function () {
                    $this->log = [];
                    try {
                        Artisan::call('migrate', ['--force' => true]);
                        $this->log[] = trim(Artisan::output()) ?: 'دیتابیس به‌روز بود.';
                        Artisan::call('optimize:clear');
                        $this->log[] = 'کش پاک شد.';
                        Notification::make()->title('به‌روزرسانی انجام شد')->success()->send();
                    } catch (\Throwable $e) {
                        $this->log[] = 'خطا: '.$e->getMessage();
                        Notification::make()->title('به‌روزرسانی ناموفق بود')->body($e->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}
