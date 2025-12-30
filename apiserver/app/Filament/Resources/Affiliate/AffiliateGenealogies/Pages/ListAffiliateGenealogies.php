<?php

namespace App\Filament\Resources\Affiliate\AffiliateGenealogies\Pages;

use App\Filament\Resources\Affiliate\AffiliateGenealogies\AffiliateGenealogyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAffiliateGenealogies extends ListRecords
{
    protected static string $resource = AffiliateGenealogyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
