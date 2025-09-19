<?php

namespace Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource;

class ViewNaukri extends ViewRecord
{
    protected static string $resource = NaukriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
