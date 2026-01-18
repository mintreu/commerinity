<?php

namespace App\Filament\Resources\Ecommerce\ProductStocks\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductStockInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('product.name')
                    ->label('Product'),
                TextEntry::make('init_quantity')
                    ->numeric(),
                TextEntry::make('sold_quantity')
                    ->numeric(),
                TextEntry::make('in_stock_quantity')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('in_stock')
                    ->boolean()
                    ->placeholder('-'),
                TextEntry::make('priority')
                    ->numeric(),
                TextEntry::make('address.title')
                    ->label('Address')
                    ->placeholder('-'),
                TextEntry::make('landing_cost')
                    ->money(),
                TextEntry::make('profit_margin')
                    ->numeric(),
                TextEntry::make('price')
                    ->money()
                    ->placeholder('-'),
                TextEntry::make('min_quantity')
                    ->numeric(),
                TextEntry::make('max_quantity')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('wholesale_unit_quantity')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('bv')
                    ->numeric(),
                TextEntry::make('pv')
                    ->numeric(),
                TextEntry::make('reward_points')
                    ->numeric(),
                TextEntry::make('commission_rate')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('is_commissionable')
                    ->boolean(),
                TextEntry::make('supplier.name')
                    ->label('Supplier')
                    ->placeholder('-'),
                TextEntry::make('purchase_invoice_number')
                    ->placeholder('-'),
                TextEntry::make('purchase_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('expiry_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('batch_number')
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('low_stock_threshold')
                    ->numeric(),
                IconEntry::make('notify_on_low_stock')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
