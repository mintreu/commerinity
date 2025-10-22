<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class TransactionChart extends ChartWidget
{
    protected ?string $heading = 'Chart';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
