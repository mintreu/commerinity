<?php

namespace App\Filament\Resources\Ecommerce\Vouchers\Schemas;

use App\Casts\ConditionMatchingCast;
use App\Casts\VoucherActionTypeCast;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                DatePicker::make('starts_from')
                    ->required(),
                DatePicker::make('ends_till')
                    ->required(),
                Toggle::make('status')
                    ->required(),
                TextInput::make('usage_per_customer')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('coupon_usage_limit')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('times_used')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('condition_type')
                    ->options(ConditionMatchingCast::class)
                    ->default('match_all')
                    ->required(),
                TextInput::make('conditions'),
                Toggle::make('end_other_rules')
                    ->required(),
                Select::make('action_type')
                    ->options(VoucherActionTypeCast::class)
                    ->required(),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('discount_step'),
                Toggle::make('apply_to_shipping')
                    ->required(),
                Toggle::make('free_shipping')
                    ->required(),
                TextInput::make('min_cart_value')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('min_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
