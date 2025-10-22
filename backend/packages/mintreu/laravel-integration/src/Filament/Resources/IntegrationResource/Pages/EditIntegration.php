<?php

namespace Mintreu\LaravelIntegration\Filament\Resources\IntegrationResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelIntegration\Filament\Resources\IntegrationResource;

class EditIntegration extends EditRecord
{
    protected static string $resource = IntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

















}
