<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource;

class ViewHelpDesk extends ViewRecord
{
    protected static string $resource = HelpDeskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('conversations')->url(fn() => self::$resource::getUrl('conversation',['record' => $this->record->uuid]))
        ];
    }
}
