<?php

namespace App\Filament\Resources\Lifecycle\UserSubscriptionResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Lifecycle\UserSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserSubscription extends EditRecord
{
    protected static string $resource = UserSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
