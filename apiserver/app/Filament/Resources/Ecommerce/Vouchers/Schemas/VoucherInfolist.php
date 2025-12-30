<?php

namespace App\Filament\Resources\Ecommerce\Vouchers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VoucherInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('starts_from')
                    ->date(),
                TextEntry::make('ends_till')
                    ->date(),
                IconEntry::make('status')
                    ->boolean(),
                TextEntry::make('usage_per_customer')
                    ->numeric(),
                TextEntry::make('coupon_usage_limit')
                    ->numeric(),
                TextEntry::make('times_used')
                    ->numeric(),
                TextEntry::make('condition_type')
                    ->badge(),
                IconEntry::make('end_other_rules')
                    ->boolean(),
                TextEntry::make('action_type')
                    ->badge(),
                TextEntry::make('discount_amount')
                    ->numeric(),
                TextEntry::make('discount_quantity')
                    ->numeric(),
                TextEntry::make('discount_step')
                    ->placeholder('-'),
                IconEntry::make('apply_to_shipping')
                    ->boolean(),
                IconEntry::make('free_shipping')
                    ->boolean(),
                TextEntry::make('min_cart_value')
                    ->numeric(),
                TextEntry::make('min_quantity')
                    ->numeric(),
                TextEntry::make('sort_order')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
