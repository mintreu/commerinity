<?php

namespace App\Filament\Resources\VoucherCodeResource\Pages;

use App\Filament\Resources\VoucherCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;


class CreateVoucherCode extends CreateRecord
{
    protected static string $resource = VoucherCodeResource::class;




    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('manage_vouchers_resource')
                ->url(fn() => VoucherCodeResource::getUrl())
                ->icon('heroicon-o-chevron-left')
                ->size('xl')
                ->tooltip('See All Coupons')
                ->label(__('Back'))
                ->color('gray')
                ->iconSize(IconSize::Medium)
                ->iconPosition(IconPosition::Before)
        ];
    }


}
