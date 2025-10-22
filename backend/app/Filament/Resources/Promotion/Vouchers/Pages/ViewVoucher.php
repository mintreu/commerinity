<?php

namespace App\Filament\Resources\Promotion\Vouchers\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\Promotion\Vouchers\VoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVoucher extends ViewRecord
{
    protected static string $resource = VoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
