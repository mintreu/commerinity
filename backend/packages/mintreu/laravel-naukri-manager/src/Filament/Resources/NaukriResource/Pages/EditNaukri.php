<?php

namespace Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource;

class EditNaukri extends EditRecord
{
    protected static string $resource = NaukriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
