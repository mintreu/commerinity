<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class PeoplesChart extends ChartWidget
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
