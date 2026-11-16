<?php

namespace App\Filament\Resources\Faqs;

use App\Filament\Resources\Faqs\Pages\ManageFaqs;
use App\Models\Faq;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = 'محتوای سایت';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'سؤال';

    protected static ?string $pluralModelLabel = 'سؤالات متداول';

    protected static ?string $recordTitleAttribute = 'question';

    public const PAGES = ['services' => 'صفحه‌ی خدمات', 'contact' => 'صفحه‌ی تماس'];

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Select::make('page')->label('صفحه')->options(self::PAGES)->required()->native(false)->default('contact'),
            TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),
            TextInput::make('question')->label('سؤال')->required()->maxLength(190)->columnSpanFull(),
            Textarea::make('answer')->label('پاسخ')->required()->rows(4)->columnSpanFull(),
            Toggle::make('is_published')->label('نمایش')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->reorderable('sort_order')
            ->columns([
                TextColumn::make('question')->label('سؤال')->searchable()->weight('bold')->wrap(),
                TextColumn::make('page')->label('صفحه')->badge()->formatStateUsing(fn ($state) => self::PAGES[$state] ?? $state),
                IconColumn::make('is_published')->label('نمایش')->boolean(),
            ])
            ->filters([SelectFilter::make('page')->label('صفحه')->options(self::PAGES), TrashedFilter::make()])
            ->recordActions([EditAction::make(), DeleteAction::make(), RestoreAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageFaqs::route('/')];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
