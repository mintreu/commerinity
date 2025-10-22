<?php

namespace App\Filament\Resources\VoucherCodeResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\VoucherCodes\VoucherCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVoucherCode extends EditRecord
{
    protected static string $resource = VoucherCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
