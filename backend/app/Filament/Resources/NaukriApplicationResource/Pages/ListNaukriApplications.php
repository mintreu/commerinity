<?php

namespace App\Filament\Resources\NaukriApplicationResource\Pages;

use App\Filament\Resources\NaukriApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNaukriApplications extends ListRecords
{
    protected static string $resource = NaukriApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
