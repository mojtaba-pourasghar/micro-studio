<?php

namespace App\Filament\Resources\Media;

use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Models\Media;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'رسانه';

    protected static ?string $modelLabel = 'فایل';

    protected static ?string $pluralModelLabel = 'کتابخانه‌ی رسانه';

    protected static ?string $recordTitleAttribute = 'path';

    public const SOURCES = ['admin' => 'مدیر', 'agent' => 'ایجنت', 'canva' => 'کانوا'];

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('alt')->label('متن جایگزین (alt)')->maxLength(190),
            TextInput::make('path')->label('مسیر (برای استفاده در فیلدها)')->disabled()->dehydrated(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id', 'desc')
            ->contentGrid(['md' => 3, 'xl' => 5])
            ->columns([
                Stack::make([
                    ImageColumn::make('path')->label('')->disk('site')->height(150)->extraImgAttributes(['style' => 'object-fit:cover;width:100%;border-radius:12px']),
                    TextColumn::make('alt')->placeholder('بدون alt')->limit(40),
                    Split::make([
                        TextColumn::make('source')->badge()->formatStateUsing(fn ($state) => self::SOURCES[$state] ?? $state),
                        TextColumn::make('size')->formatStateUsing(fn ($state) => fa_digits(number_format($state / 1024)).' KB')->color('gray'),
                    ]),
                    TextColumn::make('path')->copyable()->copyMessage('مسیر کپی شد')->size('xs')->color('gray')->limit(40),
                ])->space(2),
            ])
            ->filters([SelectFilter::make('source')->label('منبع')->options(self::SOURCES)])
            ->recordActions([
                Action::make('open')->label('باز کردن')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->url(fn (Media $r) => $r->url())->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make()->modalDescription('فایل از کتابخانه حذف می‌شود. اگر جایی از سایت از آن استفاده کند، تصویر آن بخش خالی می‌شود.')
                    ->after(fn (Media $r) => str_starts_with($r->path, 'uploads/') && @unlink(public_path($r->path))),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageMedia::route('/')];
    }
}
