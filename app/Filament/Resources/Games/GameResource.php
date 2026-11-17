<?php

namespace App\Filament\Resources\Games;

use App\Filament\Forms\Fields;
use App\Filament\Resources\Games\Pages\CreateGame;
use App\Filament\Resources\Games\Pages\EditGame;
use App\Filament\Resources\Games\Pages\ListGames;
use App\Models\Game;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static string|UnitEnum|null $navigationGroup = 'محتوای سایت';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'بازی / اپ';

    protected static ?string $pluralModelLabel = 'بازی‌ها و اپ‌ها';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->persistTabInQueryString()->tabs([
                Tab::make('اصلی')->icon(Heroicon::OutlinedInformationCircle)->schema([
                    Grid::make(3)->schema([
                        TextInput::make('title')->label('عنوان کامل')->helperText('با «:» دو بخش می‌شود؛ مثل «رازینو: ماجرای آغاز»')->required()->maxLength(190)
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, $set, $get) => $get('slug') ? null : $set('slug', Str::slug($state) ?: null)),
                        TextInput::make('short_title')->label('نام کوتاه (نشان کارت)')->required()->maxLength(60),
                        TextInput::make('slug')->label('نامک (آدرس)')->required()->unique(ignoreRecord: true)->alphaDash()->maxLength(80)->helperText('آدرس: /games/نامک'),
                        Select::make('category')->label('دسته')->options(Game::CATEGORIES)->required()->native(false)->default('game'),
                        Select::make('status')->label('وضعیت')->options(Game::STATUSES)->required()->native(false)->default('released'),
                        CheckboxList::make('platforms')->label('پلتفرم')->options(['android' => 'اندروید', 'web' => 'وب'])->default(['android'])->columns(2),
                    ]),
                    Textarea::make('summary')->label('متن کوتاه کارت')->rows(2)->maxLength(400)->required(),
                    Textarea::make('tagline')->label('متن Hero صفحه‌ی بازی')->rows(2)->maxLength(500),
                    Repeater::make('tags')->label('برچسب‌ها')->schema([
                        TextInput::make('label')->label('متن')->required()->maxLength(30),
                        Fields::tagColor(),
                    ])->columns(2)->grid(3)->defaultItems(0)->reorderable()->addActionLabel('افزودن برچسب'),
                    Grid::make(4)->schema([
                        Toggle::make('is_published')->label('نمایش در سایت')->default(true),
                        Toggle::make('is_featured')->label('بازی ویژه (صفحه‌ی بازی‌ها)'),
                        TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0),
                    ]),
                ]),
                Tab::make('تصاویر و تریلر')->icon(Heroicon::OutlinedPhoto)->schema([
                    Grid::make(2)->schema([
                        Fields::image('logo', 'لوگو / آیکن اپ (مربعی)'),
                        Fields::image('card_image', 'تصویر کارت')->required(),
                        Toggle::make('card_is_phone')->label('تصویر کارت اسکرین‌شات موبایل است (عمودی)'),
                        Fields::image('hero_image', 'پس‌زمینه‌ی Hero'),
                        Fields::image('hero_character', 'کاراکتر Hero (بدون پس‌زمینه)'),
                        Fields::image('story_image', 'تصویر بخش داستان'),
                        Fields::image('banner_image', 'پس‌زمینه‌ی بنر تریلر'),
                    ]),
                    TextInput::make('trailer_url')->label('فایل یا لینک تریلر (mp4)')->maxLength(255),
                    Repeater::make('videos')->label('ویدیوها')->schema([
                        TextInput::make('title')->label('عنوان')->maxLength(120),
                        TextInput::make('src')->label('مسیر فایل mp4')->required()->placeholder('assets/games/slug/trailer.mp4 یا uploads/...')->maxLength(255),
                        Fields::image('poster', 'تصویر پیش‌نمایش'),
                        TextInput::make('duration')->label('مدت (ثانیه)')->numeric(),
                        Toggle::make('vertical')->label('عمودی (ویدیوی موبایل)')->default(true),
                    ])->columns(2)->defaultItems(0)->reorderable()->collapsible()->addActionLabel('افزودن ویدیو')
                        ->helperText('فایل mp4 را در «کتابخانه‌ی رسانه» بارگذاری کنید و مسیرش را اینجا بگذارید.'),
                    Repeater::make('screenshots')->label('گالری تصاویر')->schema([
                        Fields::image('image', 'تصویر')->required(),
                        TextInput::make('caption')->label('توضیح')->maxLength(80),
                    ])->grid(3)->defaultItems(0)->reorderable()->collapsible()->addActionLabel('افزودن تصویر'),
                ]),
                Tab::make('داستان و ویژگی‌ها')->icon(Heroicon::OutlinedBookOpen)->schema([
                    TextInput::make('story_title')->label('تیتر بخش داستان')->helperText('[[متن توخالی]]  {{متن گرادیان}}  | = خط بعد')->maxLength(190),
                    RichEditor::make('description')->label('متن داستان')->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList', 'undo', 'redo']),
                    Fields::stringList('story_points', 'فهرست تیک‌دار زیر داستان'),
                    Repeater::make('stats')->label('آمار (نوار عددی)')->schema([
                        TextInput::make('value')->label('عدد')->required()->maxLength(12),
                        TextInput::make('label')->label('عنوان')->required()->maxLength(40),
                    ])->columns(2)->grid(4)->maxItems(4)->defaultItems(0)->addActionLabel('افزودن آمار'),
                    TextInput::make('gameplay_title')->label('تیتر بخش گیم‌پلی')->helperText('[[متن توخالی]]  {{متن گرادیان}}  | = خط بعد')->maxLength(190),
                    Repeater::make('gameplay')->label('گیم‌پلی (چطور بازی می‌شود، گام‌به‌گام)')->schema([
                        TextInput::make('title')->label('عنوان گام')->required()->maxLength(80),
                        Textarea::make('text')->label('توضیح')->rows(2)->maxLength(400),
                    ])->columns(2)->defaultItems(0)->reorderable()->collapsible()->addActionLabel('افزودن گام'),
                    Fields::stringList('benefits', 'کاربرد / مهارت‌هایی که تقویت می‌کند'),
                    Repeater::make('features')->label('ویژگی‌ها')->schema([
                        Fields::icon(),
                        TextInput::make('title')->label('عنوان')->required()->maxLength(60),
                        Textarea::make('text')->label('متن')->rows(2)->maxLength(240)->columnSpanFull(),
                    ])->columns(2)->grid(2)->defaultItems(0)->reorderable()->collapsible()->addActionLabel('افزودن ویژگی'),
                    Repeater::make('characters')->label('شخصیت‌ها')->schema([
                        Fields::image('image', 'تصویر'),
                        TextInput::make('name')->label('نام')->required()->maxLength(40),
                        TextInput::make('role')->label('نقش')->maxLength(90),
                        Toggle::make('dark')->label('کارت تیره (شخصیت منفی)'),
                    ])->grid(3)->defaultItems(0)->reorderable()->collapsible()->addActionLabel('افزودن شخصیت'),
                ]),
                Tab::make('مشخصات و دریافت')->icon(Heroicon::OutlinedArrowDownTray)->schema([
                    Grid::make(3)->schema([
                        TextInput::make('genre')->label('ژانر')->maxLength(90),
                        TextInput::make('age_range')->label('گروه سنی')->placeholder('۸ تا ۱۲ سال')->maxLength(40),
                        TextInput::make('engine')->label('موتور / فناوری')->maxLength(60),
                        TextInput::make('language')->label('زبان')->maxLength(60),
                        TextInput::make('internet')->label('نیاز به اینترنت')->maxLength(90),
                    ]),
                    Textarea::make('download_note')->label('متن بخش دریافت')->rows(2),
                    Repeater::make('store_links')->label('فروشگاه‌ها')->schema([
                        TextInput::make('store')->label('نام فروشگاه')->required()->maxLength(40),
                        TextInput::make('url')->label('لینک (خالی = «به‌زودی»)')->url()->maxLength(255),
                    ])->columns(2)->defaultItems(0)->addActionLabel('افزودن فروشگاه'),
                ]),
                Tab::make('سئو')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([
                    TextInput::make('seo_title')->label('عنوان سئو')->maxLength(70),
                    Textarea::make('seo_description')->label('توضیح سئو')->rows(2)->maxLength(170),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('card_image')->label('')->disk('site')->height(48)->width(64),
                TextColumn::make('title')->label('عنوان')->searchable()->weight('bold')->description(fn (Game $r) => $r->slug),
                TextColumn::make('category')->label('دسته')->badge()->formatStateUsing(fn ($state) => Game::CATEGORIES[$state] ?? $state),
                TextColumn::make('status')->label('وضعیت')->badge()->formatStateUsing(fn ($state) => Game::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) { 'released' => 'success', 'in_development' => 'warning', default => 'gray' }),
                IconColumn::make('is_featured')->label('ویژه')->boolean(),
                IconColumn::make('is_published')->label('نمایش')->boolean(),
                TextColumn::make('updated_at')->label('به‌روزرسانی')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->label('دسته')->options(Game::CATEGORIES),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view')->label('مشاهده')->icon(Heroicon::OutlinedEye)->url(fn (Game $r) => route('games.show', $r))->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make(), ForceDeleteBulkAction::make(), RestoreBulkAction::make()]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGames::route('/'),
            'create' => CreateGame::route('/create'),
            'edit' => EditGame::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
