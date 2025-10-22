<?php

namespace Mintreu\LaravelNaukriManager\Filament\Resources\NaukriApplicationResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriApplicationResource;

class EditNaukriApplication extends EditRecord
{
    protected static string $resource = NaukriApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
