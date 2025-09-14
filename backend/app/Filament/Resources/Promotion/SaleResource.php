<?php

namespace App\Filament\Resources\Promotion;

use App\Filament\Resources\Promotion\SaleResource\Pages;
use App\Filament\Resources\Promotion\SaleResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelCommerinity\Models\Sale;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Shop';







    public static function getCommonFormSchema():array
    {
        return [
            Forms\Components\Section::make('General')
                ->aside()
                ->description('')
                ->schema([

                    Forms\Components\TextInput::make('name')
                        ->label(__('Rule Name'))
                        ->placeholder(__('Enter Rule Name'))
                        ->required()
                        ->columnSpanFull()
                        ->hint(__('Max: 250'))
                        ->maxLength(250),

                    Forms\Components\Select::make('targets')
                        ->label('Applicable Groups')
                        ->multiple()
                        ->preload()
                        ->nullable()
                        ->columnSpanFull()
                        ->relationship('targets', 'name')
                        ->placeholder(__('Select some groups'))
                        ->helperText('Choose groups for applicable for that groups only'),

                    Forms\Components\Textarea::make('description')
                        ->placeholder('Write Briefly About This Rule')
                        ->hint(__('Max: 40,000'))
                        ->columnSpanFull()
                        ->maxLength(40000),

                    Forms\Components\Toggle::make('status')->default(false)->inline(),
                ])->columns(3),


            Forms\Components\Section::make('Rule Information')
                ->aside()
                ->description('')
                ->schema([
                    Forms\Components\DateTimePicker::make('starts_from')->required()->placeholder('Set Start Date And Time'),
                    Forms\Components\DateTimePicker::make('ends_till')->required()->placeholder('Set End Date And Time'),
                    Forms\Components\TextInput::make('sort_order')->type('number')->label('Priority')->required()->placeholder('Set Priority'),
                ])->columns(3),

            Forms\Components\Section::make('Discount Information')
                ->aside()
                ->description('')
                ->schema([
                    Forms\Components\Select::make('action_type')->options([
                        'by_percent' => 'Percentage of Product Price',
                        'by_fixed' => 'Fixed Amount',
                    ])->required()->label('Discount Type'),
                    Forms\Components\TextInput::make('discount_amount')->label('Discount Amount')->required()->placeholder('Enter Discount'),

                    Forms\Components\Select::make('end_other_rules')->options([
                        0 => 'No',
                        1 => 'Yes',
                    ])->required(),
                ])->columns(3),


        ];
    }




    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'view' => Pages\ViewSale::route('/{record}'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
