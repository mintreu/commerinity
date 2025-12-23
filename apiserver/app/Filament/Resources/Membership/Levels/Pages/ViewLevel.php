<?php

namespace App\Filament\Resources\Membership\Levels\Pages;

use App\Filament\Resources\Membership\Levels\LevelResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLevel extends ViewRecord
{
    protected static string $resource = LevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
