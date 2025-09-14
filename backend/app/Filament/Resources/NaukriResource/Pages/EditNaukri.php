<?php

namespace App\Filament\Resources\NaukriResource\Pages;

use App\Filament\Resources\NaukriResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
