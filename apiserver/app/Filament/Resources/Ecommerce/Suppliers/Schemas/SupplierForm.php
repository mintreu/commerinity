<?php

namespace App\Filament\Resources\Ecommerce\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('contact_person'),
                TextInput::make('gst_number'),
                TextInput::make('tax_number'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
