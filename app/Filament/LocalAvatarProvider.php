<?php

namespace App\Filament;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;

/** Initial-letter avatar as an inline SVG (no external service such as ui-avatars.com). */
class LocalAvatarProvider implements AvatarProvider
{
    public function get(Model $record): string
    {
        $letter = mb_substr(trim((string) ($record->name ?? 'م')), 0, 1);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="16" fill="#7457ff"/>'
            .'<text x="50%" y="54%" text-anchor="middle" dominant-baseline="middle" font-family="Vazirmatn,Tahoma,sans-serif" font-size="30" fill="#fff">'
            .htmlspecialchars($letter).'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
