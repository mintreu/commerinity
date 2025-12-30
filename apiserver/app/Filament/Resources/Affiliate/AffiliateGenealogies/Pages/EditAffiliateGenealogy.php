<?php

namespace App\Filament\Resources\Affiliate\AffiliateGenealogies\Pages;

use App\Filament\Resources\Affiliate\AffiliateGenealogies\AffiliateGenealogyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAffiliateGenealogy extends EditRecord
{
    protected static string $resource = AffiliateGenealogyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
