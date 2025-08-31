<?php

namespace App\Filament\Resources\HelpDeskTopicResource\Pages;

use App\Filament\Resources\HelpDeskTopicResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHelpDeskTopic extends EditRecord
{
    protected static string $resource = HelpDeskTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
