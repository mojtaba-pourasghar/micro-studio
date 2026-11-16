<?php

namespace App\Filament\Resources\ContentPlans\Pages;

use App\Filament\Resources\ContentPlans\ContentPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageContentPlans extends ManageRecords
{
    protected static string $resource = ContentPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
