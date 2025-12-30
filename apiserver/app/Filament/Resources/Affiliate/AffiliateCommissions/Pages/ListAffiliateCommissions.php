<?php

namespace App\Filament\Resources\Affiliate\AffiliateCommissions\Pages;

use App\Filament\Resources\Affiliate\AffiliateCommissions\AffiliateCommissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAffiliateCommissions extends ListRecords
{
    protected static string $resource = AffiliateCommissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
