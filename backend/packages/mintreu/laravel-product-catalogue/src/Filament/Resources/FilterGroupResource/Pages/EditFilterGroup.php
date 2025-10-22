<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\FilterGroupResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelProductCatalogue\Filament\Resources\FilterGroupResource;

class EditFilterGroup extends EditRecord
{
    protected static string $resource = FilterGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
