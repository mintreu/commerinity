<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource;

class EditHelpDeskFaq extends EditRecord
{
    protected static string $resource = HelpDeskFaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
