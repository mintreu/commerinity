<?php

namespace App\Filament\Resources\Addresses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AddressForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                TextInput::make('title'),
                TextInput::make('person_name')
                    ->required(),
                TextInput::make('person_email')
                    ->email(),
                TextInput::make('person_mobile')
                    ->required(),
                TextInput::make('alternate_contact'),
                Select::make('type')
                    ->options([
                        'home' => 'Home',
                        'office' => 'Office',
                        'billing' => 'Billing',
                        'shipping' => 'Shipping',
                        'warehouse' => 'Warehouse',
                        'store' => 'Store',
                    ])
                    ->default('home')
                    ->required(),
                Textarea::make('address_1')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('address_2')
                    ->columnSpanFull(),
                TextInput::make('landmark'),
                TextInput::make('city')
                    ->required(),
                TextInput::make('postal_code')
                    ->required(),
                Select::make('block_id')
                    ->relationship('block', 'name'),
                TextInput::make('state_code'),
                TextInput::make('country_code')
                    ->required()
                    ->default('IN'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                Toggle::make('default')
                    ->required(),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('pickup_location'),
                TextInput::make('addressable_type'),
                TextInput::make('addressable_id')
                    ->numeric(),
            ]);
    }
}
