<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\InquiryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelHelpdesk\Filament\Resources\InquiryResource;

class ViewInquiry extends ViewRecord
{
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
