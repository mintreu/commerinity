<?php

namespace App\Filament\Resources\Ecommerce\FilterGroups\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FilterGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               Section::make('Filter Group Information')
                ->aside()
                   ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->required(),
                ])
            ]);
    }
}
