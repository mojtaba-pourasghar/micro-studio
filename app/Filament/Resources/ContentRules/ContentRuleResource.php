<?php

namespace App\Filament\Resources\ContentRules;

use App\Filament\Resources\ContentRules\Pages\ManageContentRules;
use App\Models\ContentRule;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
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

class ContentRuleResource extends Resource
{
    protected static ?string $model = ContentRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'ایجنت هوش مصنوعی';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'قانون محتوا';

    protected static ?string $pluralModelLabel = 'قوانین محتوا';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationBadge(): ?string
    {
        $n = ContentRule::where('status', 'proposed')->count();

        return $n ? fa_digits($n) : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'پیشنهادهای منتظر تأیید';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            TextInput::make('title')->label('عنوان')->required()->maxLength(120)->columnSpan(2),
            Select::make('status')->label('وضعیت')->options(ContentRule::STATUSES)->default('approved')->native(false)->required(),
            Textarea::make('body')->label('متن قانون')->helperText('ایجنت قبل از هر کار، همه‌ی قوانین «تأییدشده» را می‌خواند و رعایت می‌کند.')->required()->rows(5)->columnSpanFull(),
            TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')->label('عنوان')->weight('bold')->searchable(),
                TextColumn::make('body')->label('متن')->limit(90)->wrap(),
                TextColumn::make('status')->label('وضعیت')->badge()->formatStateUsing(fn ($state) => ContentRule::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) { 'approved' => 'success', 'proposed' => 'warning', default => 'gray' }),
                TextColumn::make('proposed_by')->label('پیشنهاد')->formatStateUsing(fn ($state) => match ($state) { 'agent' => 'ایجنت', 'system' => 'پیش‌فرض', default => 'مدیر' }),
            ])
            ->filters([SelectFilter::make('status')->label('وضعیت')->options(ContentRule::STATUSES)])
            ->recordActions([
                Action::make('approve')->label('تأیید')->icon(Heroicon::OutlinedCheck)->color('success')
                    ->visible(fn (ContentRule $r) => $r->status !== 'approved')
                    ->action(fn (ContentRule $r) => $r->update(['status' => 'approved', 'approved_at' => now()])),
                Action::make('archive')->label('بایگانی')->icon(Heroicon::OutlinedArchiveBox)->color('gray')
                    ->visible(fn (ContentRule $r) => $r->status !== 'archived')
                    ->action(fn (ContentRule $r) => $r->update(['status' => 'archived'])),
                EditAction::make(), DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageContentRules::route('/')];
    }
}
