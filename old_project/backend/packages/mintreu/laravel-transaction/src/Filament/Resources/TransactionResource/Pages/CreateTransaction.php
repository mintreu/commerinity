<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\TransactionResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Mintreu\LaravelTransaction\Filament\Resources\TransactionResource;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;
}
