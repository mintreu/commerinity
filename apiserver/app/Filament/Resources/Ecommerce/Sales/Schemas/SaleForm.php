<?php

namespace App\Filament\Resources\Ecommerce\Sales\Schemas;

use App\Casts\ConditionMatchingCast;
use App\Casts\SaleActionTypeCast;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                DateTimePicker::make('starts_from')
                    ->required(),
                DateTimePicker::make('ends_till')
                    ->required(),
                Toggle::make('status')
                    ->required(),
                Select::make('condition_type')
                    ->options(ConditionMatchingCast::class)
                    ->default('match_all')
                    ->required(),
                TextInput::make('conditions'),
                Toggle::make('end_other_rules')
                    ->required(),
                Select::make('action_type')
                    ->options(SaleActionTypeCast::class)
                    ->required(),
                TextInput::make('discount_amount')
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
