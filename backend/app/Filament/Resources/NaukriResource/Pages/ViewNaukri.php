<?php

namespace App\Filament\Resources\NaukriResource\Pages;

use App\Filament\Resources\NaukriResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

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
