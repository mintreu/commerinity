<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource;

class EditHelpDesk extends EditRecord
{
    protected static string $resource = HelpDeskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
