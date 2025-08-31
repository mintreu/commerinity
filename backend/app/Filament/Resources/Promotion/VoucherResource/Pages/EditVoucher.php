<?php

namespace App\Filament\Resources\Promotion\VoucherResource\Pages;

use App\Filament\Resources\Promotion\VoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVoucher extends EditRecord
{
    protected static string $resource = VoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
