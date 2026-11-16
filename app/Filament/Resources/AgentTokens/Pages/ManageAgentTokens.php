<?php

namespace App\Filament\Resources\AgentTokens\Pages;

use App\Filament\Resources\AgentTokens\AgentTokenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAgentTokens extends ManageRecords
{
    protected static string $resource = AgentTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
