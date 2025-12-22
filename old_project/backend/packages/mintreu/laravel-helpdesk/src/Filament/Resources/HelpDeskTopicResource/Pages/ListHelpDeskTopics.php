<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource;

class ListHelpDeskTopics extends ListRecords
{
    protected static string $resource = HelpDeskTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
