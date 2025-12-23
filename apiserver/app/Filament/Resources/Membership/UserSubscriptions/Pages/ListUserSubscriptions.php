<?php

namespace App\Filament\Resources\Membership\UserSubscriptions\Pages;

use App\Filament\Resources\Membership\UserSubscriptions\UserSubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserSubscriptions extends ListRecords
{
    protected static string $resource = UserSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
