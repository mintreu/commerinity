<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource;

class ListHelpDesks extends ListRecords
{
    protected static string $resource = HelpDeskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Create Ticket'),
        ];
    }
}
