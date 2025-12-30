<?php

namespace App\Filament\Resources\Ecommerce\Orders\Pages;

use App\Filament\Resources\Ecommerce\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
