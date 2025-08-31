<?php

namespace App\Filament\Resources\HelpDeskFaqResource\Pages;

use App\Filament\Resources\HelpDeskFaqResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
