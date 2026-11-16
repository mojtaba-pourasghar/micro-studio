<?php

namespace App\Filament\Resources\Blocks;

use App\Filament\Forms\Fields;
use App\Filament\Resources\Blocks\Pages\ManageBlocks;
use App\Models\Block;
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
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class BlockResource extends Resource
{
    protected static ?string $model = Block::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'محتوای سایت';

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'کارت';

    protected static ?string $pluralModelLabel = 'کارت‌ها و آمار';

    protected static ?string $recordTitleAttribute = 'title';

    public const GROUPS = [
        'honors' => 'دستاوردها (صفحه‌ی اصلی و درباره ما)',
        'promises' => 'قول ما به خانواده‌ها (بازی‌ها)',
        'values' => 'ارزش‌ها (درباره ما)',
        'steps' => 'فرایند کار (خدمات)',
        'tools' => 'ابزارها (خدمات)',
        'support' => 'پشتیبانی والدین (تماس)',
        'contact_steps' => 'مراحل پس از ارسال فرم (تماس)',
    ];

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Select::make('group')->label('بخش')->options(self::GROUPS)->required()->native(false)->live(),
            TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),
            TextInput::make('title')->label('عنوان')->required()->maxLength(190)->columnSpanFull(),
            Textarea::make('text')->label('متن')->rows(2)->columnSpanFull(),
            TextInput::make('value')->label(fn ($get) => $get('group') === 'steps' ? 'خروجی مرحله' : 'عدد')
                ->visible(fn ($get) => in_array($get('group'), ['honors', 'steps'], true))->maxLength(60),
            TextInput::make('label')->label('برچسب کنار')->visible(fn ($get) => $get('group') === 'honors')->maxLength(60),
            Fields::icon()->visible(fn ($get) => in_array($get('group'), ['promises', 'values', 'tools', 'support'], true)),
            TextInput::make('link_label')->label('متن لینک')->visible(fn ($get) => $get('group') === 'support')->maxLength(60),
            TextInput::make('link_url')->label('آدرس لینک')->visible(fn ($get) => $get('group') === 'support')->maxLength(255),
            Toggle::make('is_published')->label('نمایش')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->reorderable('sort_order')
            ->defaultGroup(Group::make('group')->label('بخش')->getTitleFromRecordUsing(fn (Block $r) => self::GROUPS[$r->group] ?? $r->group))
            ->columns([
                TextColumn::make('title')->label('عنوان')->searchable()->weight('bold')->wrap(),
                TextColumn::make('value')->label('عدد/خروجی'),
                TextColumn::make('text')->label('متن')->limit(60),
                IconColumn::make('is_published')->label('نمایش')->boolean(),
            ])
            ->filters([SelectFilter::make('group')->label('بخش')->options(self::GROUPS), TrashedFilter::make()])
            ->recordActions([EditAction::make(), DeleteAction::make(), RestoreAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageBlocks::route('/')];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
