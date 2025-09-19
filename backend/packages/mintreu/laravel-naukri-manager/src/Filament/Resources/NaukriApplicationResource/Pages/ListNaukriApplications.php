<?php

namespace Mintreu\LaravelNaukriManager\Filament\Resources\NaukriApplicationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriApplicationResource;

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
