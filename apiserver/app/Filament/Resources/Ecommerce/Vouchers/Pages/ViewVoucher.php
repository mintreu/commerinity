<?php

namespace App\Filament\Resources\Ecommerce\Vouchers\Pages;

use App\Filament\Resources\Ecommerce\Vouchers\VoucherResource;
use Filament\Actions\EditAction;
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
