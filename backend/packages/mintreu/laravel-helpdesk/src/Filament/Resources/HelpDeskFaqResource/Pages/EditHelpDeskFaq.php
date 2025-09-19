<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource;

class EditHelpDeskFaq extends EditRecord
{
    protected static string $resource = HelpDeskFaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
