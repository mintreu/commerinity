<?php

namespace App\Filament\Resources\Ecommerce\Orders\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('order_number'),
                TextEntry::make('customerable_type')
                    ->placeholder('-'),
                TextEntry::make('customerable_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('subtotal')
                    ->numeric(),
                TextEntry::make('shipping_cost')
                    ->money(),
                TextEntry::make('tax')
                    ->numeric(),
                TextEntry::make('discount')
                    ->numeric(),
                TextEntry::make('total')
                    ->numeric(),
                TextEntry::make('total_bv')
                    ->numeric(),
                TextEntry::make('total_pv')
                    ->numeric(),
                TextEntry::make('total_reward_points')
                    ->numeric(),
                IconEntry::make('commission_processed')
                    ->boolean(),
                TextEntry::make('shippingAddress.title')
                    ->label('Shipping address')
                    ->placeholder('-'),
                TextEntry::make('billingAddress.title')
                    ->label('Billing address')
                    ->placeholder('-'),
                TextEntry::make('expire_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('delivered_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('return_period_ends_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('completed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('voucher')
                    ->placeholder('-'),
                TextEntry::make('tracking_id')
                    ->placeholder('-'),
                IconEntry::make('payment_success')
                    ->boolean(),
                TextEntry::make('quantity')
                    ->numeric(),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('admin_notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
