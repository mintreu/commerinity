<?php

namespace App\Filament\Resources\Lifecycle\LevelResource\Pages;

use App\Filament\Resources\Lifecycle\LevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLevels extends ListRecords
{
    protected static string $resource = LevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
