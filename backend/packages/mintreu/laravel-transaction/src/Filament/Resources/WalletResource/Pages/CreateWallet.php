<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource;

class CreateWallet extends CreateRecord
{
    protected static string $resource = WalletResource::class;
}
