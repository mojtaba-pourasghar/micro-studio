<?php

namespace App\Filament\Resources\AgentTokens\Pages;

use App\Filament\Resources\AgentTokens\AgentTokenResource;
use App\Models\AgentToken;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageAgentTokens extends ManageRecords
{
    protected static string $resource = AgentTokenResource::class;

    public function getSubheading(): ?string
    {
        return 'آدرس سرور MCP برای افزودن به کلاد (Connectors): '.url('/mcp/{token}').' — به‌جای {token} توکن ساخته‌شده را بگذارید.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('ساخت توکن تازه')->icon(Heroicon::OutlinedPlus)
                ->using(function (array $data) {
                    [$token, $plain] = AgentToken::issue($data['name'], $data['abilities'] ?? []);
                    Notification::make()->title('توکن ساخته شد — فقط همین یک بار نمایش داده می‌شود')
                        ->body("آدرس کانکتور کلاد:\n".url('/mcp/'.$plain)."\n\nیا هدر:\nAuthorization: Bearer ".$plain)
                        ->persistent()->success()->send();

                    return $token;
                })
                ->successNotification(null),
        ];
    }
}
