<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\InquiryResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Mintreu\LaravelHelpdesk\Filament\Resources\InquiryResource;

class ListInquiries extends ListRecords
{
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
