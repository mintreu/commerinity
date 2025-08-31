<?php

namespace App\Filament\Resources\HelpDeskFaqResource\Pages;

use App\Filament\Resources\HelpDeskFaqResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHelpDeskFaq extends EditRecord
{
    protected static string $resource = HelpDeskFaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
