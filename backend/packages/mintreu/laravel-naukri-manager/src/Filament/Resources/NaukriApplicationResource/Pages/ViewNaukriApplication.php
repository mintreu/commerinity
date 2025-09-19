<?php

namespace Mintreu\LaravelNaukriManager\Filament\Resources\NaukriApplicationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriApplicationResource;

class ViewNaukriApplication extends ViewRecord
{
    protected static string $resource = NaukriApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
