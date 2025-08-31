<?php

namespace App\Filament\Resources\HelpDeskResource\Pages;

use App\Filament\Resources\HelpDeskResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHelpDesk extends ViewRecord
{
    protected static string $resource = HelpDeskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
