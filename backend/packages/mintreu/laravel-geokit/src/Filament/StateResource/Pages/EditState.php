<?php

namespace Mintreu\LaravelGeokit\Filament\StateResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelGeokit\Filament\StateResource;

class EditState extends EditRecord
{
    protected static string $resource = StateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
