<?php

namespace App\Filament\Resources\SmsProviders\Pages;

use App\Filament\Resources\SmsProviders\SmsProviderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSmsProvider extends ViewRecord
{
    protected static string $resource = SmsProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
