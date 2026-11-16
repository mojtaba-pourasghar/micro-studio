<?php

namespace App\Filament\Resources\AgentTokens;

use App\Filament\Resources\AgentTokens\Pages\ManageAgentTokens;
use App\Models\AgentToken;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AgentTokenResource extends Resource
{
    protected static ?string $model = AgentToken::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|UnitEnum|null $navigationGroup = 'ایجنت هوش مصنوعی';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'توکن ایجنت';

    protected static ?string $pluralModelLabel = 'اتصال کلاد (توکن‌ها)';

    protected static ?string $recordTitleAttribute = 'name';

    /** Default abilities for a new token: everything except the restricted ones. */
    public static function defaultAbilities(): array
    {
        return array_values(array_diff(array_keys(AgentToken::ABILITIES), AgentToken::RESTRICTED));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('نام (مثلاً «کلاد - چت» یا «کلاد - زمان‌بندی»)')->required()->maxLength(60),
            CheckboxList::make('abilities')->label('دسترسی‌ها')->options(AgentToken::ABILITIES)->default(static::defaultAbilities())
                ->helperText('انتشار مستقیم مطلب و تأیید قوانین به‌صورت پیش‌فرض خاموش است: مطالب ایجنت منتظر تأیید شما می‌مانند.')->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('نام')->weight('bold'),
                TextColumn::make('abilities')->label('دسترسی‌ها')->badge()->formatStateUsing(fn ($state) => AgentToken::ABILITIES[$state] ?? $state)->limitList(3),
                TextColumn::make('last_used_at')->label('آخرین استفاده')->since()->placeholder('هرگز'),
                TextColumn::make('revoked_at')->label('وضعیت')->badge()->formatStateUsing(fn ($state) => $state ? 'لغوشده' : 'فعال')
                    ->color(fn ($state) => $state ? 'danger' : 'success')->default('فعال'),
            ])
            ->recordActions([
                Action::make('revoke')->label('لغو')->icon(Heroicon::OutlinedNoSymbol)->color('danger')->requiresConfirmation()
                    ->visible(fn (AgentToken $r) => ! $r->revoked_at)->action(fn (AgentToken $r) => $r->update(['revoked_at' => now()])),
                EditAction::make(), DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAgentTokens::route('/')];
    }
}
