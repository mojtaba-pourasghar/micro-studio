<?php

namespace App\Filament\Resources\Services;

use App\Filament\Forms\Fields;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Models\Service;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = 'محتوای سایت';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'خدمت';

    protected static ?string $pluralModelLabel = 'خدمات';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('متن')->columnSpanFull()->schema([
                Grid::make(3)->schema([
                    TextInput::make('title')->label('عنوان')->required()->maxLength(90),
                    TextInput::make('slug')->label('نامک (لنگر #svc-…)')->required()->alphaDash()->unique(ignoreRecord: true)->maxLength(40),
                    TextInput::make('cta_label')->label('متن دکمه')->placeholder('سفارش ساخت بازی')->maxLength(60),
                ]),
                Textarea::make('summary')->label('متن کوتاه (اسلایدر صفحه‌ی اصلی)')->rows(2)->required()->maxLength(300),
                Textarea::make('description')->label('متن کامل (صفحه‌ی خدمات)')->rows(3)->maxLength(800),
                Fields::stringList('deliverables', 'آنچه تحویل می‌گیرید'),
            ]),
            Section::make('تصاویر')->columnSpanFull()->columns(2)->schema([
                Fields::image('image', 'تصویر اصلی'),
                Fields::image('character_image', 'کاراکتر (بدون پس‌زمینه)'),
                Toggle::make('image_is_phone')->label('تصویر اصلی اسکرین‌شات موبایل است'),
            ]),
            Grid::make(3)->columnSpanFull()->schema([
                Toggle::make('is_published')->label('نمایش در سایت')->default(true),
                TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('character_image')->label('')->disk('site')->height(48),
                TextColumn::make('title')->label('عنوان')->searchable()->weight('bold'),
                TextColumn::make('summary')->label('متن')->limit(70),
                IconColumn::make('is_published')->label('نمایش')->boolean(),
            ])
            ->filters([TrashedFilter::make()])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make(), ForceDeleteBulkAction::make(), RestoreBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
