<?php

namespace App\Filament\Resources\NaukriApplicationResource\Pages;

use App\Filament\Resources\NaukriApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

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
