<?php

namespace App\Filament\Resources\Media\Pages;

use App\Filament\Resources\Media\MediaResource;
use App\Support\MediaStore;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ManageMedia extends ManageRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')->label('بارگذاری')->icon(Heroicon::OutlinedArrowUpTray)
                ->schema([
                    FileUpload::make('files')->label('تصاویر')->multiple()->required()->storeFiles(false)->maxSize(10240)
                        ->acceptedFileTypes(array_keys(MediaStore::MIMES)),
                    TextInput::make('alt')->label('متن جایگزین (برای همه)')->maxLength(190),
                ])
                ->action(function (array $data) {
                    $n = 0;
                    foreach ((array) $data['files'] as $file) {
                        if ($file instanceof TemporaryUploadedFile) {
                            MediaStore::fromUpload($file, $data['alt'] ?? null, 'admin');
                            $n++;
                        }
                    }
                    Notification::make()->title(fa_digits($n).' فایل بارگذاری شد')->success()->send();
                }),
        ];
    }
}
