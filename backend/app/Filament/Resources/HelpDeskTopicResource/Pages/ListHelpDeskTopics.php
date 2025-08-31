<?php

namespace App\Filament\Resources\HelpDeskTopicResource\Pages;

use App\Filament\Resources\HelpDeskTopicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
