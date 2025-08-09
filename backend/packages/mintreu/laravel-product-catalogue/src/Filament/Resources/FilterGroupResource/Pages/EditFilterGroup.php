<?php

namespace Mintreu\LaravelProductCatalogue\FIlament\Resources\FilterGroupResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelProductCatalogue\FIlament\Resources\FilterGroupResource;

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
