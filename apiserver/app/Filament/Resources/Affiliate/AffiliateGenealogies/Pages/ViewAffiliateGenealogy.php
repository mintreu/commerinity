<?php

namespace App\Filament\Resources\Affiliate\AffiliateGenealogies\Pages;

use App\Filament\Resources\Affiliate\AffiliateGenealogies\AffiliateGenealogyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAffiliateGenealogy extends ViewRecord
{
    protected static string $resource = AffiliateGenealogyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
