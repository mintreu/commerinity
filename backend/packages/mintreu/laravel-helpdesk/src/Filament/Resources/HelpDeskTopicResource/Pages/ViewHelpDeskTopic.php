<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource;

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
