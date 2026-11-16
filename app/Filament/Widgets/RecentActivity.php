<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Models\ActivityLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentActivity extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'آخرین تغییرات سایت';

    public function table(Table $table): Table
    {
        return $table
            ->query(ActivityLog::query()->latest('id')->limit(10))
            ->paginated(false)
            ->columns([
                TextColumn::make('created_at')->label('زمان')->since(),
                TextColumn::make('actor')->label('توسط')->badge()->color(fn ($state) => str_starts_with($state, 'agent:') ? 'info' : 'gray')
                    ->formatStateUsing(fn ($state) => str_starts_with($state, 'agent:') ? 'ایجنت · '.substr($state, 6) : (str_starts_with($state, 'admin:') ? substr($state, 6) : $state)),
                TextColumn::make('action')->label('کار')->formatStateUsing(fn ($state) => ActivityLogResource::ACTIONS[$state] ?? $state),
                TextColumn::make('summary')->label('مورد')->wrap(),
            ]);
    }
}
