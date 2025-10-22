<?php

namespace App\Filament\Resources\Order;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Order\OrderResource\Pages\ListOrders;
use App\Filament\Resources\Order\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\Order\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\Order\OrderResource\Pages\EditOrder;
use App\Filament\Resources\Order\OrderResource\Pages;
use App\Filament\Resources\Order\OrderResource\RelationManagers;
use App\Models\Order\Order;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup ='Order Management';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->maxLength(255),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                TextInput::make('discount')
                    ->required()
                    ->numeric(),
                TextInput::make('tax')
                    ->required()
                    ->numeric(),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                TextInput::make('voucher')
                    ->maxLength(255),
                Toggle::make('is_cod')
                    ->required(),
                TextInput::make('tracking_id')
                    ->maxLength(255),
                TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('pending'),
                Toggle::make('payment_success')
                    ->required(),
                DateTimePicker::make('expire_at'),
                TextInput::make('customerable_type')
                    ->maxLength(255),
                TextInput::make('customerable_id')
                    ->numeric(),
                Toggle::make('has_guest')
                    ->required(),
                TextInput::make('customer_name')
                    ->maxLength(255),
                TextInput::make('customer_email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('customer_mobile')
                    ->maxLength(255),
                Toggle::make('shipping_is_billing')
                    ->required(),
                Select::make('billing_address_id')
                    ->relationship('billingAddress', 'title'),
                Select::make('shipping_address_id')
                    ->relationship('shippingAddress', 'title'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('voucher')
                    ->searchable(),
                IconColumn::make('is_cod')
                    ->boolean(),
                TextColumn::make('tracking_id')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                IconColumn::make('payment_success')
                    ->boolean(),
                TextColumn::make('expire_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('customerable_type')
                    ->searchable(),
                TextColumn::make('customerable_id')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('has_guest')
                    ->boolean(),
                TextColumn::make('customer_name')
                    ->searchable(),
                TextColumn::make('customer_email')
                    ->searchable(),
                TextColumn::make('customer_mobile')
                    ->searchable(),
                IconColumn::make('shipping_is_billing')
                    ->boolean(),
                TextColumn::make('billingAddress.title')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('shippingAddress.title')
                    ->numeric()
                    ->sortable(),
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
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
