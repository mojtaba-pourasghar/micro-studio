<?php

namespace App\Filament\Resources\ContentPlans\Pages;

use App\Filament\Resources\ContentPlans\ContentPlanResource;
use App\Support\ContentPlanImporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageContentPlans extends ManageRecords
{
    protected static string $resource = ContentPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')->label('ورود از اکسل / CSV')->icon(Heroicon::OutlinedArrowUpTray)->color('gray')
                ->modalDescription('ستون‌ها (ردیف اول): title (موضوع)، brief (توضیح)، type (post / game_update / image / other)، due_date (تاریخ میلادی 2026-12-01 یا شمسی 1405/09/10)، game (نامک بازی)، category (نامک دسته). فقط ستون title الزامی است؛ عنوان ستون‌ها می‌تواند فارسی هم باشد: موضوع، توضیح، نوع، تاریخ، بازی، دسته.')
                ->schema([
                    FileUpload::make('file')->label('فایل xlsx یا csv')->required()->disk('local')->directory('imports')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel']),
                ])
                ->action(function (array $data) {
                    $path = storage_path('app/private/'.$data['file']);
                    if (! is_file($path)) {
                        $path = storage_path('app/'.$data['file']);
                    }
                    [$created, $errors] = ContentPlanImporter::import($path);
                    @unlink($path);
                    Notification::make()->title(fa_digits($created).' برنامه وارد شد')
                        ->body($errors ? implode("\n", array_slice($errors, 0, 5)) : null)
                        ->{$errors ? 'warning' : 'success'}()->send();
                }),
            CreateAction::make(),
        ];
    }
}
