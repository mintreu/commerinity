<?php

namespace App\Filament\Resources\HelpDeskResource\Pages;

use App\Filament\Resources\HelpDeskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHelpDesk extends EditRecord
{
    protected static string $resource = HelpDeskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
