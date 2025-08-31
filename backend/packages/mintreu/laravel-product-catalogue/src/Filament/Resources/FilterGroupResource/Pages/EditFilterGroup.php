<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\FilterGroupResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterGroupResource;

class EditFilterGroup extends EditRecord
{
    protected static string $resource = FilterGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
