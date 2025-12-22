<?php

namespace App\Filament\Resources\Lifecycle\UserSubscriptionResource\Pages;

use App\Filament\Resources\Lifecycle\UserSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserSubscription extends ViewRecord
{
    protected static string $resource = UserSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
