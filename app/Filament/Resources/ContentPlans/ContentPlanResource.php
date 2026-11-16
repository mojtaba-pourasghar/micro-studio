<?php

namespace App\Filament\Resources\ContentPlans;

use App\Filament\Resources\ContentPlans\Pages\ManageContentPlans;
use App\Models\ContentPlan;
use App\Support\Jalali;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ContentPlanResource extends Resource
{
    protected static ?string $model = ContentPlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'ایجنت هوش مصنوعی';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'برنامه';

    protected static ?string $pluralModelLabel = 'تقویم محتوا';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('title')->label('عنوان / موضوع')->required()->maxLength(190)->columnSpanFull(),
            Textarea::make('brief')->label('توضیح برای ایجنت')->helperText('هر چیزی که ایجنت باید بداند: زاویه‌ی مطلب، نکات کلیدی، سبک تصویر…')->rows(4)->columnSpanFull(),
            Select::make('type')->label('نوع کار')->options(ContentPlan::TYPES)->default('post')->native(false)->required(),
            DatePicker::make('due_date')->label('تاریخ انجام'),
            Select::make('game_id')->label('بازی مرتبط')->relationship('game', 'title')->native(false)->searchable()->preload(),
            Select::make('post_category_id')->label('دسته‌ی وبلاگ')->relationship('category', 'name')->native(false)->preload(),
            Select::make('status')->label('وضعیت')->options(ContentPlan::STATUSES)->default('planned')->native(false)->required(),
            Select::make('post_id')->label('مطلب ساخته‌شده')->relationship('post', 'title')->native(false)->searchable(),
            Textarea::make('result_note')->label('گزارش ایجنت')->rows(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('due_date')
            ->columns([
                TextColumn::make('due_date')->label('تاریخ')->formatStateUsing(fn ($state) => Jalali::format($state))->sortable(),
                TextColumn::make('title')->label('موضوع')->weight('bold')->searchable()->wrap()->description(fn (ContentPlan $r) => $r->game?->short_title),
                TextColumn::make('type')->label('نوع')->badge()->formatStateUsing(fn ($state) => ContentPlan::TYPES[$state] ?? $state),
                TextColumn::make('status')->label('وضعیت')->badge()->formatStateUsing(fn ($state) => ContentPlan::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) { 'done' => 'success', 'in_progress' => 'info', 'skipped' => 'gray', default => 'warning' }),
                TextColumn::make('result_note')->label('گزارش')->limit(50)->toggleable(),
            ])
            ->filters([SelectFilter::make('status')->label('وضعیت')->options(ContentPlan::STATUSES), SelectFilter::make('type')->label('نوع')->options(ContentPlan::TYPES)])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageContentPlans::route('/')];
    }
}
