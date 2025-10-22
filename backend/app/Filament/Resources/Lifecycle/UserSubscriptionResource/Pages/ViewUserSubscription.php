<?php

namespace App\Filament\Resources\Lifecycle\UserSubscriptions\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\Lifecycle\UserSubscriptions\UserSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUserSubscription extends ViewRecord
{
    protected static string $resource = UserSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
