<?php

namespace App\Filament\Resources\Promotion\VoucherResource\Pages;

use App\Filament\Resources\Promotion\VoucherResource;
use App\Filament\Resources\VoucherCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;

class ListVouchers extends ListRecords
{
    protected static string $resource = VoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-pencil-square')
                ->size('xl')
                ->tooltip('Create Voucher')
                ->label(__('New Voucher'))
                ->color('primary')
                ->iconSize(IconSize::Large)
                ->iconPosition(IconPosition::Before)
            ,

            Actions\Action::make('manage_vouchers_resource')
                ->label(__('Manage Vouchers'))
                ->url(fn() => VoucherCodeResource::getUrl())
                ->icon('heroicon-o-chevron-left')
                ->size('xl')
                ->tooltip('See All Coupons')
                ->label(__('Back to Coupons'))
                ->color('info')
                ->iconSize(IconSize::Large)
                ->iconPosition(IconPosition::Before)



        ];
    }
}
