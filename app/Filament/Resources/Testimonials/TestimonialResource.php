<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Forms\Fields;
use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Models\Testimonial;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'محتوای سایت';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'نظر';

    protected static ?string $pluralModelLabel = 'نظرات خانواده‌ها';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->label('نام')->required()->maxLength(90),
            TextInput::make('role')->label('نقش')->maxLength(90),
            Textarea::make('quote')->label('متن نظر')->required()->rows(3)->maxLength(400)->columnSpanFull(),
            TextInput::make('rating')->label('امتیاز (۱ تا ۵)')->numeric()->minValue(1)->maxValue(5)->default(5),
            Toggle::make('is_published')->label('نمایش')->default(true),
            TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort_order')->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')->label('نام')->searchable()->weight('bold')->description(fn ($record) => $record->role),
                TextColumn::make('quote')->label('نظر')->limit(80),
                IconColumn::make('is_published')->label('نمایش')->boolean(),
            ])
            ->filters([TrashedFilter::make()])
            ->recordActions([EditAction::make(), DeleteAction::make(), RestoreAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTestimonials::route('/')];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
