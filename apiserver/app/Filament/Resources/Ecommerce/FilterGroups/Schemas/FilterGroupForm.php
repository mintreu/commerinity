<?php

namespace App\Filament\Resources\Ecommerce\FilterGroups\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FilterGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
