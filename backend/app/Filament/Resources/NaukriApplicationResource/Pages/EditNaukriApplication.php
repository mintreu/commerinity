<?php

namespace App\Filament\Resources\NaukriApplicationResource\Pages;

use App\Filament\Resources\NaukriApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNaukriApplication extends EditRecord
{
    protected static string $resource = NaukriApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
