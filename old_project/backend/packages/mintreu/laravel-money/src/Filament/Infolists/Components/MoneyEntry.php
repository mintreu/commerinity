<?php

namespace Mintreu\LaravelMoney\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry;
use Mintreu\LaravelMoney\LaravelMoney;

class MoneyEntry extends TextEntry
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->money(LaravelMoney::defaultCurrency(),100);

    }

}
