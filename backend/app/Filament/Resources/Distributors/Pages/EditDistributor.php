<?php

namespace App\Filament\Resources\Distributors\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Distributors\DistributorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDistributor extends EditRecord
{
    protected static string $resource = DistributorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
