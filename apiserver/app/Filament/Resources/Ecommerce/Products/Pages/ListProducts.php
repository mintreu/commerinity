<?php

namespace App\Filament\Resources\Ecommerce\Products\Pages;

use App\Casts\ProductTypeCast;
use App\Filament\Resources\Ecommerce\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;

class ListProducts extends ListRecords
{
    protected Width | string | null $maxContentWidth = 'full';
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }




    public function getTabs(): array
    {
        return array_merge(
            array_combine(
                array_map(fn($case) => $case->value, ProductTypeCast::cases()),
                array_map(fn($case) => Tab::make()
                    ->icon($case->getIcon())
                    ->modifyQueryUsing(fn( $query) => $query->where('type', $case->value)),
                    ProductTypeCast::cases())
            ),
            [
                'all' => Tab::make()->icon('heroicon-s-table-cells'),
            ]
        );

    }





}
