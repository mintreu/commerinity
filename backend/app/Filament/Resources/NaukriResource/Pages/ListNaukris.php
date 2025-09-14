<?php

namespace App\Filament\Resources\NaukriResource\Pages;

use App\Filament\Resources\NaukriResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNaukris extends ListRecords
{
    protected static string $resource = NaukriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
