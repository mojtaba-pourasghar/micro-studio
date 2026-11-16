<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ManageActivityLogs;
use App\Models\ActivityLog;
use App\Support\Rollback;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'ایجنت هوش مصنوعی';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'رویداد';

    protected static ?string $pluralModelLabel = 'گزارش تغییرات';

    public const ACTIONS = ['created' => 'ایجاد', 'updated' => 'ویرایش', 'deleted' => 'حذف', 'restored' => 'بازیابی', 'rolled_back' => 'بازگردانی'];

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        $flat = fn ($state) => collect($state ?? [])->map(fn ($v) => is_scalar($v) || $v === null ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE))->all();

        return $schema->columns(2)->components([
            TextEntry::make('actor')->label('توسط'),
            TextEntry::make('created_at')->label('زمان')->dateTime(),
            TextEntry::make('summary')->label('مورد')->columnSpanFull(),
            KeyValueEntry::make('before')->label('قبل')->state(fn ($record) => $flat($record->before)),
            KeyValueEntry::make('after')->label('بعد')->state(fn ($record) => $flat($record->after)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('زمان')->since()->sortable(),
                TextColumn::make('actor')->label('توسط')->badge()->color(fn ($state) => str_starts_with($state, 'agent:') ? 'info' : 'gray')
                    ->formatStateUsing(fn ($state) => str_starts_with($state, 'agent:') ? 'ایجنت · '.substr($state, 6) : (str_starts_with($state, 'admin:') ? substr($state, 6) : $state)),
                TextColumn::make('action')->label('کار')->badge()->formatStateUsing(fn ($state) => self::ACTIONS[$state] ?? $state)
                    ->color(fn ($state) => match ($state) { 'created' => 'success', 'deleted' => 'danger', 'updated' => 'warning', default => 'gray' }),
                TextColumn::make('summary')->label('مورد')->searchable()->wrap(),
                TextColumn::make('after')->label('فیلدها')->formatStateUsing(fn ($record) => implode('، ', array_keys($record->after ?? $record->before ?? [])))->limit(60)->toggleable(),
            ])
            ->filters([
                SelectFilter::make('action')->label('کار')->options(self::ACTIONS),
                Filter::make('agent')->label('فقط کارهای ایجنت')->query(fn (Builder $q) => $q->where('actor', 'like', 'agent:%')),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('rollback')->label('بازگرداندن')->icon(Heroicon::OutlinedArrowUturnLeft)->color('danger')
                    ->visible(fn (ActivityLog $r) => in_array($r->action, ['updated', 'deleted', 'created'], true))
                    ->requiresConfirmation()->modalDescription(fn (ActivityLog $r) => match ($r->action) {
                        'updated' => 'فیلدهای تغییرکرده به مقدار قبلی برمی‌گردند.',
                        'deleted' => 'مورد حذف‌شده بازیابی می‌شود.',
                        default => 'مورد ساخته‌شده حذف (نرم) می‌شود.',
                    })
                    ->action(function (ActivityLog $r) {
                        $msg = Rollback::apply($r);
                        Notification::make()->title($msg)->{str_starts_with($msg, '✓') ? 'success' : 'warning'}()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageActivityLogs::route('/')];
    }
}
