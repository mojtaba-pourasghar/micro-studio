<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static string|UnitEnum|null $navigationGroup = 'ارتباطات';

    protected static ?string $modelLabel = 'پیام';

    protected static ?string $pluralModelLabel = 'پیام‌های تماس';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $n = ContactMessage::whereNull('read_at')->count();

        return $n ? fa_digits($n) : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextEntry::make('name')->label('نام'),
            TextEntry::make('subject')->label('موضوع')->placeholder('—'),
            TextEntry::make('email')->label('ایمیل')->url(fn ($record) => 'mailto:'.$record->email)->copyable(),
            TextEntry::make('phone')->label('تلفن')->placeholder('—')->copyable(),
            TextEntry::make('message')->label('پیام')->columnSpanFull(),
            IconEntry::make('newsletter')->label('عضویت خبرنامه')->boolean(),
            TextEntry::make('created_at')->label('زمان')->dateTime(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id', 'desc')
            ->recordClasses(fn (ContactMessage $r) => $r->read_at ? null : 'font-bold')
            ->columns([
                IconColumn::make('read_at')->label('')->boolean()->trueIcon(Heroicon::OutlinedEnvelopeOpen)->falseIcon(Heroicon::Envelope)
                    ->state(fn ($record) => (bool) $record->read_at)->falseColor('warning')->trueColor('gray'),
                TextColumn::make('name')->label('نام')->searchable()->description(fn ($record) => $record->email),
                TextColumn::make('subject')->label('موضوع')->badge(),
                TextColumn::make('message')->label('پیام')->limit(70),
                TextColumn::make('created_at')->label('زمان')->since()->sortable(),
            ])
            ->filters([TernaryFilter::make('read_at')->label('خوانده‌شده')->nullable()])
            ->recordActions([
                ViewAction::make()->after(fn (ContactMessage $r) => $r->read_at ?: $r->update(['read_at' => now()])),
                Action::make('reply')->label('پاسخ')->icon(Heroicon::OutlinedPaperAirplane)->url(fn ($record) => 'mailto:'.$record->email.'?subject='.rawurlencode('پاسخ میکرو استودیو'))->openUrlInNewTab(),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageContactMessages::route('/')];
    }
}
