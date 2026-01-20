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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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
            ->components([
                TextInput::make('init_quantity')
                    ->required()
                    ->numeric(),
                TextInput::make('sold_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('in_stock_quantity')
                    ->numeric(),
                Toggle::make('in_stock'),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('address_id')
                    ->relationship('address', 'title'),
                TextInput::make('landing_cost')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('$'),
                TextInput::make('profit_margin')
                    ->required()
                    ->numeric()
                    ->default(0.0),
//                TextInput::make('price')
//                    ->numeric()
//                    ->prefix('$'),
                TextInput::make('min_quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('max_quantity')
                    ->numeric(),
                TextInput::make('wholesale_unit_quantity')
                    ->numeric(),
                TextInput::make('bv')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('pv')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('reward_points')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('commission_rate')
                    ->numeric(),
                Toggle::make('is_commissionable')
                    ->required(),
                Select::make('supplier_id')
                    ->relationship('supplier', 'name'),
                TextInput::make('purchase_invoice_number'),
                DatePicker::make('purchase_date'),
                DatePicker::make('expiry_date'),
                TextInput::make('batch_number'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('low_stock_threshold')
                    ->required()
                    ->numeric()
                    ->default(5),
                Toggle::make('notify_on_low_stock')
                    ->required(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('product_id'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_id')
            ->columns([
                TextColumn::make('init_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sold_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('in_stock_quantity')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('in_stock')
                    ->boolean(),
                TextColumn::make('priority')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('address.title')
                    ->searchable(),
                TextColumn::make('landing_cost')
                    ->money()
                    ->sortable(),
                TextColumn::make('profit_margin')
                    ->numeric()
                    ->sortable(),
//                TextColumn::make('price')
//                    ->money()
//                    ->sortable(),
                TextColumn::make('min_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('wholesale_unit_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('bv')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pv')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reward_points')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('commission_rate')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_commissionable')
                    ->boolean(),
                TextColumn::make('supplier.name')
                    ->searchable(),
                TextColumn::make('purchase_invoice_number')
                    ->searchable(),
                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('batch_number')
                    ->searchable(),
                TextColumn::make('low_stock_threshold')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('notify_on_low_stock')
                    ->boolean(),
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
                AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
