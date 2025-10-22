<?php

namespace App\Filament\Resources\Lifecycle\UserSubscriptions\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Lifecycle\UserSubscriptions\UserSubscriptionResource;
use Filament\Actions;
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
