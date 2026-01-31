<?php

namespace App\Filament\Resources\Ecommerce\Products\Pages;

use App\Filament\Resources\Ecommerce\Products\ProductResource;
use BackedEnum;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;


use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ManageStock extends ManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'stocks';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Stock Levels')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('init_quantity')
                                    ->label('Initial Quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->placeholder('0'),
                                TextInput::make('sold_quantity')
                                    ->label('Sold Quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->readOnly()
                                    ->placeholder('0'),
                                TextInput::make('in_stock_quantity')
                                    ->label('Available Stock')
                                    ->numeric()
                                    ->minValue(0)
                                    ->placeholder('Calculated: init - sold'),
                            ]),
                        Toggle::make('in_stock')
                            ->disabled()
                            ->label('In Stock'),
                        TextInput::make('priority')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->collapsible(),
                Section::make('Pricing')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('landing_cost')
                                    ->label('Landing Cost (paise)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->helperText('Total purchase cost in paise'),

                                TextInput::make('profit_margin')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%')
                                    ->placeholder('0.0')
                                    ->helperText('Profit margin percentage for this purchase entry'),
                            ]),

                        Placeholder::make('stock_price_control')
                            ->label('Selling Price')
                            ->content('The final selling price, BV, PV, and reward points are managed on the main product form. Stock entries only capture landing cost, margin, and inventory constraints.')
                            ->helperText('Leave price-related fields blank here to avoid overrides.'),
                    ])
                    ->collapsible(),
                Section::make('Purchase Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('supplier_id')
                                    ->relationship('supplier', 'name')
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('purchase_invoice_number'),
                            ])
                            ->extraAttributes(['class' => 'gap-4']),
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('purchase_date'),
                                DatePicker::make('expiry_date'),
                                TextInput::make('batch_number'),
                            ]),
                    ])
                    ->collapsible(),
                Section::make('Inventory Limits')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('min_quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1),
                                TextInput::make('max_quantity')
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('low_stock_threshold')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(5),
                            ]),
                        Toggle::make('notify_on_low_stock')
                            ->label('Notify on Low Stock'),
                    ])
                    ->collapsible(),
                Section::make('Commission & Rewards')
                    ->schema([
                        Placeholder::make('commission_hint')
                            ->label('Product-level values')
                            ->content('BV, PV, reward points, and commission settings are managed on the main product form. Stock entries only track inventory, landing cost, and purchase metadata.')
                    ])
                    ->collapsible(),
                Section::make('Other')
                    ->schema([
                        Select::make('address_id')
                            ->relationship('address', 'title')
                            ->searchable()
                            ->preload(),
                        TextInput::make('wholesale_unit_quantity')
                            ->numeric()
                            ->minValue(0),
                        Textarea::make('notes')
                            ->columnSpanFull()
                            ->rows(3),
                    ])
                    ->collapsible(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Stock Overview')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('init_quantity')
                                    ->label('Initial')
                                    ->badge()
                                    ->color(fn (int $state): string => match (true) {
                                        $state > 100 => 'success',
                                        $state > 10 => 'warning',
                                        default => 'danger',
                                    }),
                                TextEntry::make('sold_quantity')
                                    ->label('Sold')
                                    ->badge()
                                    ->color('danger'),
                                TextEntry::make('in_stock_quantity')
                                    ->label('Available')
                                    ->badge()
                                    ->color(fn (int $state): string => match (true) {
                                        $state > 50 => 'success',
                                        $state > 5 => 'warning',
                                        default => 'danger',
                                    }),
                                IconEntry::make('in_stock')
                                    ->label('Status')
                                    ->boolean()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                            ]),
                    ]),
                Section::make('Pricing & Purchase')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('price')
                                    ->money('USD')
                                    ->label('Selling Price'),
                                TextEntry::make('landing_cost')
                                    ->money('USD')
                                    ->label('Cost'),
                            ])
                            ->extraAttributes(['class' => 'gap-6']),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('purchase_date')
                                    ->date()
                                    ->label('Purchased'),
                                TextEntry::make('expiry_date')
                                    ->date()
                                    ->label('Expires'),
                                TextEntry::make('purchase_invoice_number')
                                    ->label('Invoice #'),
                            ]),
                    ])
                    ->collapsible(),
                Section::make('Details')
                    ->schema([
                        TextEntry::make('priority')
                            ->badge(),
                        TextEntry::make('batch_number')
                            ->label('Batch'),
                        TextEntry::make('supplier.name')
                            ->label('Supplier'),
                        TextEntry::make('address.title')
                            ->label('Location'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                TextColumn::make('sold_quantity')
                    ->numeric()
                    ->label('Stock (Sold)')
                    ->sortable(),
                TextColumn::make('in_stock_quantity')
                    ->numeric()
                    ->label('Stock (Available)')
                    ->sortable(),
                IconColumn::make('in_stock')
                    ->boolean(),
                TextColumn::make('priority')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),

                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('batch_number')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                //AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
              //  DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                  //  DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
