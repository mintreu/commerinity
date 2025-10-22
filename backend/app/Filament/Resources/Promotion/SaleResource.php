<?php

namespace App\Filament\Resources\Promotion;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;
use App\Filament\Resources\Promotion\SaleResource\RelationManagers\SaleProductsRelationManager;
use App\Filament\Resources\Promotion\SaleResource\Pages\ListSales;
use App\Filament\Resources\Promotion\SaleResource\Pages\CreateSale;
use App\Filament\Resources\Promotion\SaleResource\Pages\ViewSale;
use App\Filament\Resources\Promotion\SaleResource\Pages\EditSale;
use App\Filament\Resources\Promotion\SaleResource\Pages;
use App\Filament\Resources\Promotion\SaleResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Mintreu\LaravelCommerinity\Casts\SaleActionTypeCast;
use Mintreu\LaravelCommerinity\Models\Sale;
use Mintreu\LaravelMoney\LaravelMoney;


class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Shop';







    public static function getCommonFormSchema():array
    {
        return [
            Section::make('General')
                ->aside()
                ->description('')
                ->schema([

                    TextInput::make('name')
                        ->label(__('Rule Name'))
                        ->placeholder(__('Enter Rule Name'))
                        ->required()
                        ->columnSpanFull()
                        ->hint(__('Max: 250'))
                        ->maxLength(250),

                    Select::make('targets')
                        ->label('Applicable Groups')
                        ->multiple()
                        ->preload()
                        ->nullable()
                        ->columnSpanFull()
                        ->relationship('targets', 'name')
                        ->placeholder(__('Select some groups'))
                        ->helperText('Choose groups for applicable for that groups only'),

                    Textarea::make('description')
                        ->placeholder('Write Briefly About This Rule')
                        ->hint(__('Max: 40,000'))
                        ->columnSpanFull()
                        ->maxLength(40000),

                    Toggle::make('status')->default(false)->inline(),
                ])->columns(3),


            Section::make('Rule Information')
                ->aside()
                ->description('')
                ->schema([
                    DateTimePicker::make('starts_from')->required()->placeholder('Set Start Date And Time'),
                    DateTimePicker::make('ends_till')->required()->placeholder('Set End Date And Time'),
                    TextInput::make('sort_order')->type('number')->label('Priority')->required()->placeholder('Set Priority'),
                ])->columns(3),

            Section::make('Discount Information')
                ->aside()
                ->description('')
                ->schema([
                    Select::make('action_type')
                        ->options(
                            collect(SaleActionTypeCast::cases())
                                ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
                                ->toArray()
                        )
                        ->required()
                        ->helperText(fn($state) => $state ? SaleActionTypeCast::tryFrom($state)->getDescription() : null)
                        ->live()
                        ->label('Discount Type'),

                    TextInput::make('discount_amount')
                        ->label('Discount Value')
                        ->helperText('Enter percentage or if fixed amount, enter in paisa')
                        ->hint(function (Get $get, $state) {
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





                    Select::make('end_other_rules')->options([
                        0 => 'No',
                        1 => 'Yes',
                    ])->required(),
                ])->columns(3),


        ];
    }




    public static function getRelations(): array
    {
        return [
            SaleProductsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSales::route('/'),
            'create' => CreateSale::route('/create'),
            'view' => ViewSale::route('/{record}'),
            'edit' => EditSale::route('/{record}/edit'),
        ];
    }
}
