<?php

namespace App\Filament\Resources\Affiliate\AffiliateCommissions\Pages;

use App\Filament\Resources\Affiliate\AffiliateCommissions\AffiliateCommissionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAffiliateCommission extends ViewRecord
{
    protected static string $resource = AffiliateCommissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
