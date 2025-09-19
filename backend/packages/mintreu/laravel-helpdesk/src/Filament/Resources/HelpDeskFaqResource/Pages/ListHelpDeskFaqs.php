<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource;

class ListHelpDeskFaqs extends ListRecords
{
    protected static string $resource = HelpDeskFaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
