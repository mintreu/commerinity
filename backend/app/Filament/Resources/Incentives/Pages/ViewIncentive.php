<?php

namespace App\Filament\Resources\Incentives\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\Incentives\IncentiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewIncentive extends ViewRecord
{
    protected static string $resource = IncentiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
