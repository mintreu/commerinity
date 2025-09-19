<?php

namespace App\Filament\Resources\VoucherCodeResource\Pages;

use App\Filament\Resources\Promotion\VoucherResource;
use App\Filament\Resources\VoucherCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;

class ListVoucherCodes extends ListRecords
{
    protected static string $resource = VoucherCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-m-pencil-square')
                ->size('xl')
                ->tooltip('Create Coupon')
                ->label(__('New Coupon'))
                ->color('primary')
                ->iconSize(IconSize::Large)
                ->iconPosition(IconPosition::Before)
            ,


            Actions\Action::make('manage_vouchers_resource')
                ->label(__('Manage Vouchers'))
                ->url(fn() => VoucherResource::getUrl())
                ->icon('heroicon-o-chevron-left')
                ->size('xl')
                ->tooltip('See All Vouchers')
                ->label(__('Back to Vouchers'))
                ->color('info')
                ->iconSize(IconSize::Large)
                ->iconPosition(IconPosition::Before)
        ];
    }
}
