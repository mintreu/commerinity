<?php

namespace App\Filament\Resources\VoucherCodeResource\Pages;

use App\Filament\Resources\VoucherCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVoucherCode extends ViewRecord
{
    protected static string $resource = VoucherCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
