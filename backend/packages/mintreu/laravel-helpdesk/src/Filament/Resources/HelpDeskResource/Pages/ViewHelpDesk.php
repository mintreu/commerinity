<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages;

use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource;

class ViewHelpDesk extends ViewRecord
{
    protected static string $resource = HelpDeskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('conversations')->url(fn() => self::$resource::getUrl('conversation',['record' => $this->record->uuid]))
        ];
    }
}
