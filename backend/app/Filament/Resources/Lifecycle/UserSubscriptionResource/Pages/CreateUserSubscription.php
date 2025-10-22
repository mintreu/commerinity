<?php

namespace App\Filament\Resources\Lifecycle\UserSubscriptions\Pages;

use App\Filament\Resources\Lifecycle\UserSubscriptions\UserSubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserSubscription extends CreateRecord
{
    protected static string $resource = UserSubscriptionResource::class;
}
