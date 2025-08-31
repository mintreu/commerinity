<?php

namespace App\Filament\Resources\HelpDeskTopicResource\Pages;

use App\Filament\Resources\HelpDeskTopicResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHelpDeskTopic extends ViewRecord
{
    protected static string $resource = HelpDeskTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
