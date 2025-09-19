<?php

namespace App\Filament\Resources\Promotion;

use App\Filament\Resources\Promotion\VoucherResource\Pages;
use App\Filament\Resources\Promotion\VoucherResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelCommerinity\Models\Voucher;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Shop';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('starts_from'),
                Forms\Components\DatePicker::make('ends_till'),
                Forms\Components\Toggle::make('status')
                    ->required(),
                Forms\Components\TextInput::make('usage_per_customer')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('coupon_usage_limit')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('times_used')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('condition_type')
                    ->required(),
                Forms\Components\TextInput::make('conditions'),
                Forms\Components\Toggle::make('end_other_rules')
                    ->required(),
                Forms\Components\TextInput::make('action_type')
                    ->maxLength(255),
                Forms\Components\TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0000),
                Forms\Components\TextInput::make('discount_quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('discount_step')
                    ->required()
                    ->maxLength(255)
                    ->default(1),
                Forms\Components\Toggle::make('apply_to_shipping')
                    ->required(),
                Forms\Components\Toggle::make('free_shipping')
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('starts_from')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_till')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('usage_per_customer')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('coupon_usage_limit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('times_used')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('condition_type')
                    ->boolean(),
                Tables\Columns\IconColumn::make('end_other_rules')
                    ->boolean(),
                Tables\Columns\TextColumn::make('action_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_step')
                    ->searchable(),
                Tables\Columns\IconColumn::make('apply_to_shipping')
                    ->boolean(),
                Tables\Columns\IconColumn::make('free_shipping')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
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
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'view' => Pages\ViewVoucher::route('/{record}'),
            'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}
