<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterResource;

class EditFilter extends EditRecord
{
    protected static string $resource = FilterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
