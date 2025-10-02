<?php

namespace App\Filament\Resources\Promotion;

use App\Filament\Resources\Promotion\SaleResource\Pages;
use App\Filament\Resources\Promotion\SaleResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Mintreu\LaravelCommerinity\Casts\SaleActionTypeCast;
use Mintreu\LaravelCommerinity\Models\Sale;
use Mintreu\LaravelMoney\Filament\Forms\Components\MoneyInput;
use Mintreu\LaravelMoney\LaravelMoney;


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
                    Forms\Components\Select::make('action_type')
                        ->options(
                            collect(SaleActionTypeCast::cases())
                                ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
                                ->toArray()
                        )
                        ->required()
                        ->helperText(fn($state) => $state ? SaleActionTypeCast::tryFrom($state)->getDescription() : null)
                        ->live()
                        ->label('Discount Type'),

                    Forms\Components\TextInput::make('discount_amount')
                        ->label('Discount Value')
                        ->helperText('Enter percentage or if fixed amount, enter in paisa')
                        ->hint(function (Forms\Get $get, $state) {
                            if (! $state) {
                                return null;
                            }

                            if (in_array($get('action_type'), [
                                SaleActionTypeCast::BY_PERCENT->value,
                                SaleActionTypeCast::TO_PERCENT->value,
                            ])) {
                                return $state . '%';
                            }

                            return LaravelMoney::make($state);
                        })
                        ->integer()
                        ->inputMode('decimal')
                        ->minValue(1)
                        ->required()
                        ->reactive() // <-- important to re-render when state changes
                        ->placeholder('Enter Discount'),





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
            RelationManagers\SaleProductsRelationManager::class
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
