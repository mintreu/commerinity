<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource;

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
