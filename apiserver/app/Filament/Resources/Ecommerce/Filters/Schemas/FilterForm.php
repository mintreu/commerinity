<?php

namespace App\Filament\Resources\Ecommerce\Filters\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FilterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Information')
                    ->description('Product Filter Has Many Options')
                    ->aside()
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        Toggle::make('is_required')
                            ->required(),



                        Select::make('filter_group_id')
                            ->label('Filter Group')
                            ->multiple()
                            ->relationship('groups','name')


                    ])->columnSpanFull()
            ]);
    }
}
