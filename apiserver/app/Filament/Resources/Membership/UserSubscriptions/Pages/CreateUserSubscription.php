<?php

namespace App\Filament\Resources\Membership\UserSubscriptions\Pages;

use App\Filament\Resources\Membership\UserSubscriptions\UserSubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserSubscription extends CreateRecord
{
    protected static string $resource = UserSubscriptionResource::class;
}
