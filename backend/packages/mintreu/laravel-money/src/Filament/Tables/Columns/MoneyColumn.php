<?php

namespace Mintreu\LaravelMoney\Filament\Tables\Columns;

use Filament\Tables\Columns\TextColumn;
use Mintreu\LaravelMoney\LaravelMoney;

class MoneyColumn extends TextColumn
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->money(LaravelMoney::defaultCurrency(),100);
    }

}
