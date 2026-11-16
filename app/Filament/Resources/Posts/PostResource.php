<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Forms\Fields;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'وبلاگ';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'مطلب';

    protected static ?string $pluralModelLabel = 'مطالب';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationBadge(): ?string
    {
        $n = Post::where('status', 'pending_review')->count();

        return $n ? fa_digits($n) : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'در انتظار تأیید شما';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make('متن مطلب')->columnSpan(2)->schema([
                TextInput::make('title')->label('عنوان')->required()->maxLength(190)
                    ->live(onBlur: true)->afterStateUpdated(fn ($state, $set, $get) => $get('slug') ? null : $set('slug', Str::slug($state) ?: null)),
                TextInput::make('slug')->label('نامک (آدرس)')->required()->unique(ignoreRecord: true)->maxLength(120)->helperText('آدرس: /blog/نامک — حروف لاتین و خط تیره'),
                Textarea::make('excerpt')->label('خلاصه')->rows(2)->maxLength(400)->required(),
                Textarea::make('body')->label('متن کامل (HTML)')->rows(22)->extraInputAttributes(['dir' => 'rtl', 'style' => 'font-family:monospace;font-size:13px'])
                    ->helperText('تگ‌های مجاز: p, h2, h3, strong, em, a, ul, ol, li, blockquote, figure, img, table. کلاس‌های ویژه: p.prose-lead (لید)، ul.prose-stats (آمار)، figure.prose-figure. اسکریپت و استایل حذف می‌شوند.'),
            ]),
            Grid::make(1)->columnSpan(1)->schema([
                Section::make('انتشار')->schema([
                    Select::make('status')->label('وضعیت')->options(Post::STATUSES)->required()->native(false)->default('draft'),
                    DateTimePicker::make('published_at')->label('تاریخ انتشار')->helperText('خالی = زمان تأیید'),
                    Toggle::make('is_featured')->label('مطلب ویژه‌ی وبلاگ'),
                    Textarea::make('review_note')->label('یادداشت بررسی (برای ایجنت)')->rows(2),
                    TextInput::make('created_by')->label('نویسنده‌ی اولیه')->disabled()->dehydrated(false),
                ]),
                Section::make('دسته و ارتباط')->schema([
                    Select::make('post_category_id')->label('دسته')->relationship('category', 'name')->native(false)->preload(),
                    Select::make('game_id')->label('بازی مرتبط')->relationship('game', 'title')->native(false)->searchable()->preload(),
                    TagsInput::make('tags')->label('برچسب‌ها'),
                    TextInput::make('author')->label('نام نویسنده')->default('تیم میکرو استودیو')->maxLength(90),
                ]),
                Section::make('تصویر و سئو')->schema([
                    Fields::image('cover_image', 'تصویر شاخص'),
                    TextInput::make('seo_title')->label('عنوان سئو')->maxLength(70),
                    Textarea::make('seo_description')->label('توضیح سئو')->rows(2)->maxLength(170),
                ]),
            ]),
        ]);
    }

    public static function publishAction(): Action
    {
        return Action::make('publish')->label('تأیید و انتشار')->icon(Heroicon::OutlinedCheckBadge)->color('success')
            ->visible(fn (Post $r) => $r->status !== 'published')
            ->requiresConfirmation()->modalDescription('مطلب بلافاصله در سایت منتشر می‌شود.')
            ->action(function (Post $r) {
                $r->update(['status' => 'published', 'published_at' => $r->published_at ?? now(), 'review_note' => null]);
                Notification::make()->title('مطلب منتشر شد')->success()->send();
            });
    }

    public static function rejectAction(): Action
    {
        return Action::make('reject')->label('برگرداندن با یادداشت')->icon(Heroicon::OutlinedArrowUturnLeft)->color('danger')
            ->visible(fn (Post $r) => in_array($r->status, ['pending_review', 'published'], true))
            ->schema([Textarea::make('review_note')->label('چه چیزی باید اصلاح شود؟')->required()->rows(3)])
            ->action(function (Post $r, array $data) {
                $r->update(['status' => 'rejected', 'review_note' => $data['review_note']]);
                Notification::make()->title('مطلب برای اصلاح برگشت')->send();
            });
    }

    public static function previewAction(): Action
    {
        return Action::make('preview')->label('پیش‌نمایش')->icon(Heroicon::OutlinedEye)->color('gray')
            ->url(fn (Post $r) => route('blog.show', $r))->openUrlInNewTab();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                ImageColumn::make('cover_image')->label('')->disk('site')->height(44)->width(64),
                TextColumn::make('title')->label('عنوان')->searchable()->weight('bold')->limit(60)->description(fn (Post $r) => $r->category?->name),
                TextColumn::make('status')->label('وضعیت')->badge()->formatStateUsing(fn ($state) => Post::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => match ($state) { 'published' => 'success', 'pending_review' => 'warning', 'rejected' => 'danger', default => 'gray' }),
                TextColumn::make('created_by')->label('توسط')->badge()->formatStateUsing(fn ($state) => $state === 'agent' ? 'ایجنت' : 'مدیر')
                    ->color(fn ($state) => $state === 'agent' ? 'info' : 'gray'),
                IconColumn::make('is_featured')->label('ویژه')->boolean(),
                TextColumn::make('published_at')->label('انتشار')->formatStateUsing(fn ($state) => \App\Support\Jalali::format($state))->sortable(),
                TextColumn::make('updated_at')->label('ویرایش')->since()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('وضعیت')->options(Post::STATUSES),
                SelectFilter::make('post_category_id')->label('دسته')->relationship('category', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([static::previewAction(), static::publishAction(), EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make(), ForceDeleteBulkAction::make(), RestoreBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
