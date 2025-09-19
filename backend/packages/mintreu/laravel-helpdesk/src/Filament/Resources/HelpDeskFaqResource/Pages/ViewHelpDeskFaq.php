<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource;

class ViewHelpDeskFaq extends ViewRecord
{
    protected static string $resource = HelpDeskFaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
