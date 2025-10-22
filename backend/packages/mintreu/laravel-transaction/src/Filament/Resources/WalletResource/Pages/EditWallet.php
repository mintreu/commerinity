<?php

namespace Mintreu\LaravelTransaction\Filament\Resources\WalletResource\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mintreu\LaravelTransaction\Filament\Resources\WalletResource;

class EditWallet extends EditRecord
{
    protected static string $resource = WalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
