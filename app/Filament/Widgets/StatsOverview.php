<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\ContentPlan;
use App\Models\ContentRule;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pending = Post::where('status', 'pending_review')->count();
        $rules = ContentRule::where('status', 'proposed')->count();
        $due = ContentPlan::whereIn('status', ['planned', 'in_progress'])->whereDate('due_date', '<=', today())->count();
        $unread = ContactMessage::whereNull('read_at')->count();
        $agentToday = ActivityLog::where('actor', 'like', 'agent:%')->where('created_at', '>=', now()->subDay())->count();

        return [
            Stat::make('مطالب منتظر تأیید', fa_digits($pending))->color($pending ? 'warning' : 'gray')->icon('heroicon-o-newspaper')
                ->url(route('filament.admin.resources.posts.index', ['filters' => ['status' => ['value' => 'pending_review']]])),
            Stat::make('قوانین پیشنهادی', fa_digits($rules))->color($rules ? 'warning' : 'gray')->icon('heroicon-o-scale')
                ->url(route('filament.admin.resources.content-rules.index')),
            Stat::make('برنامه‌های سررسیده', fa_digits($due))->icon('heroicon-o-calendar-days')
                ->url(route('filament.admin.resources.content-plans.index')),
            Stat::make('پیام‌های خوانده‌نشده', fa_digits($unread))->color($unread ? 'info' : 'gray')->icon('heroicon-o-inbox')
                ->url(route('filament.admin.resources.contact-messages.index')),
            Stat::make('کارهای ایجنت (۲۴ ساعت)', fa_digits($agentToday))->icon('heroicon-o-cpu-chip')
                ->url(route('filament.admin.resources.activity-logs.index')),
        ];
    }
}
