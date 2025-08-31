<?php

namespace App\Filament\Resources\HelpDeskResource\Pages;

use App\Filament\Resources\HelpDeskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHelpDesks extends ListRecords
{
    protected static string $resource = HelpDeskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
