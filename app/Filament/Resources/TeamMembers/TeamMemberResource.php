<?php

namespace App\Filament\Resources\TeamMembers;

use App\Filament\Forms\Fields;
use App\Filament\Resources\TeamMembers\Pages\ManageTeamMembers;
use App\Models\TeamMember;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
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

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'محتوای سایت';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'عضو تیم';

    protected static ?string $pluralModelLabel = 'تیم';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->label('نام')->required()->maxLength(90),
            TextInput::make('role')->label('نقش / توضیح کوتاه')->maxLength(120),
            TextInput::make('tag')->label('برچسب')->maxLength(30),
            Fields::tagColor('tag_color'),
            Textarea::make('bio')->label('معرفی')->rows(2)->columnSpanFull(),
            Fields::image('avatar', 'تصویر / آواتار', watermark: false),
            Toggle::make('is_published')->label('نمایش')->default(true),
            TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->reorderable('sort_order')
            ->columns([
                ImageColumn::make('avatar')->label('')->disk('site')->height(44),
                TextColumn::make('name')->label('نام')->searchable()->weight('bold')->description(fn ($record) => $record->role),
                TextColumn::make('tag')->label('برچسب')->badge(),
                IconColumn::make('is_published')->label('نمایش')->boolean(),
            ])
            ->filters([TrashedFilter::make()])
            ->recordActions([EditAction::make(), DeleteAction::make(), RestoreAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTeamMembers::route('/')];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
