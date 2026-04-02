<?php

declare(strict_types=1);

namespace App\Filament\Resources\Advertisements\Schemas\Traits;

use App\Services\Advertisement\AdvertisementFormConfigService;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

trait HasAdvertisementDynamicConfiguration
{
    protected function formConfigService(): AdvertisementFormConfigService
    {
        return app(AdvertisementFormConfigService::class);
    }

    protected function blockField(): Select
    {
        return Select::make('block')
            ->label('Placement Block')
            ->options(fn (Get $get): array => $this->formConfigService()->getBlockOptions((string) $get('placement')))
            ->searchable()
            ->helperText('Logical block within the selected placement.')
            ->nullable();
    }

    protected function syncPositionTypeWithPlacement(Select $field): Select
    {
        return $field
            ->afterStateUpdated(function (?string $state, Set $set): void {
                $set('position_type', $this->formConfigService()->getDefaultPositionTypeForPlacement($state));
            });
    }
}
