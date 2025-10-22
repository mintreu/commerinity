<?php

namespace App\Filament\Resources\Promotion\Vouchers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Promotion\Vouchers\Pages\ListVouchers;
use App\Filament\Resources\Promotion\Vouchers\Pages\CreateVoucher;
use App\Filament\Resources\Promotion\Vouchers\Pages\ViewVoucher;
use App\Filament\Resources\Promotion\Vouchers\Pages\EditVoucher;
use App\Filament\Resources\Promotion\VoucherResource\Pages;
use App\Filament\Resources\Promotion\VoucherResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelCommerinity\Models\Voucher;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Shop';


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->maxLength(255),
                TextInput::make('description')
                    ->maxLength(255),
                DatePicker::make('starts_from'),
                DatePicker::make('ends_till'),
                Toggle::make('status')
                    ->required(),
                TextInput::make('usage_per_customer')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('coupon_usage_limit')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('times_used')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('condition_type')
                    ->required(),
                TextInput::make('conditions'),
                Toggle::make('end_other_rules')
                    ->required(),
                TextInput::make('action_type')
                    ->maxLength(255),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0000),
                TextInput::make('discount_quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('discount_step')
                    ->required()
                    ->maxLength(255)
                    ->default(1),
                Toggle::make('apply_to_shipping')
                    ->required(),
                Toggle::make('free_shipping')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('description')
                    ->searchable(),
                TextColumn::make('starts_from')
                    ->date()
                    ->sortable(),
                TextColumn::make('ends_till')
                    ->date()
                    ->sortable(),
                IconColumn::make('status')
                    ->boolean(),
                TextColumn::make('usage_per_customer')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('coupon_usage_limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('times_used')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('condition_type')
                    ->boolean(),
                IconColumn::make('end_other_rules')
                    ->boolean(),
                TextColumn::make('action_type')
                    ->searchable(),
                TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_step')
                    ->searchable(),
                IconColumn::make('apply_to_shipping')
                    ->boolean(),
                IconColumn::make('free_shipping')
                    ->boolean(),
                TextColumn::make('sort_order')
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
            'index' => ListVouchers::route('/'),
            'create' => CreateVoucher::route('/create'),
            'view' => ViewVoucher::route('/{record}'),
            'edit' => EditVoucher::route('/{record}/edit'),
        ];
    }
}
