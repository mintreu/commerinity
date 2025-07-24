<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Casts\ProductTypeCast;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
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
