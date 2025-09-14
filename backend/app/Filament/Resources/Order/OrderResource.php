<?php

namespace App\Filament\Resources\Order;

use App\Filament\Resources\Order\OrderResource\Pages;
use App\Filament\Resources\Order\OrderResource\RelationManagers;
use App\Models\Order\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('discount')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('tax')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('voucher')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_cod')
                    ->required(),
                Forms\Components\TextInput::make('tracking_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('pending'),
                Forms\Components\Toggle::make('payment_success')
                    ->required(),
                Forms\Components\DateTimePicker::make('expire_at'),
                Forms\Components\TextInput::make('customerable_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('customerable_id')
                    ->numeric(),
                Forms\Components\Toggle::make('has_guest')
                    ->required(),
                Forms\Components\TextInput::make('customer_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('customer_email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('customer_mobile')
                    ->maxLength(255),
                Forms\Components\Toggle::make('shipping_is_billing')
                    ->required(),
                Forms\Components\Select::make('billing_address_id')
                    ->relationship('billingAddress', 'title'),
                Forms\Components\Select::make('shipping_address_id')
                    ->relationship('shippingAddress', 'title'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('voucher')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_cod')
                    ->boolean(),
                Tables\Columns\TextColumn::make('tracking_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\IconColumn::make('payment_success')
                    ->boolean(),
                Tables\Columns\TextColumn::make('expire_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customerable_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customerable_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('has_guest')
                    ->boolean(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_mobile')
                    ->searchable(),
                Tables\Columns\IconColumn::make('shipping_is_billing')
                    ->boolean(),
                Tables\Columns\TextColumn::make('billingAddress.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shippingAddress.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
