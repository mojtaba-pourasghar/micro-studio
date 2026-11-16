<?php

namespace App\Filament\Resources\ContentRules\Pages;

use App\Filament\Resources\ContentRules\ContentRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageContentRules extends ManageRecords
{
    protected static string $resource = ContentRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
