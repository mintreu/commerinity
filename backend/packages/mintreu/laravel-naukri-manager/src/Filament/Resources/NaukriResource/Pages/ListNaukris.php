<?php

namespace Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource;

class ListNaukris extends ListRecords
{
    protected static string $resource = NaukriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
