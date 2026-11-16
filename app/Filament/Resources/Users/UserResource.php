<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static string|UnitEnum|null $navigationGroup = 'سیستم';

    protected static ?string $modelLabel = 'مدیر';

    protected static ?string $pluralModelLabel = 'مدیران';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->label('نام')->required()->maxLength(90),
            TextInput::make('email')->label('ایمیل')->email()->required()->unique(ignoreRecord: true),
            TextInput::make('password')->label('رمز عبور')->password()->revealable()->minLength(10)
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->helperText('برای ویرایش، خالی بگذارید تا رمز تغییر نکند.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('نام')->weight('bold'),
                TextColumn::make('email')->label('ایمیل'),
                TextColumn::make('created_at')->label('ساخته‌شده')->since(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()->hidden(fn (User $r) => $r->is(auth()->user()))]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageUsers::route('/')];
    }
}
