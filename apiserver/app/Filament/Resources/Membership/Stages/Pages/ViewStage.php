<?php

namespace App\Filament\Resources\Membership\Stages\Pages;

use App\Filament\Resources\Membership\Stages\StageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStage extends ViewRecord
{
    protected static string $resource = StageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
