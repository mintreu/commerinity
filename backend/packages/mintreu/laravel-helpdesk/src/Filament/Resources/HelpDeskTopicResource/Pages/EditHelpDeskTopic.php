<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource;

class EditHelpDeskTopic extends EditRecord
{
    protected static string $resource = HelpDeskTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
