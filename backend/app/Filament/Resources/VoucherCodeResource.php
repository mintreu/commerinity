<?php

namespace App\Filament\Resources\VoucherCodes;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\VoucherCodeResource\Pages\ListVoucherCodes;
use App\Filament\Resources\VoucherCodeResource\Pages\CreateVoucherCode;
use App\Filament\Resources\VoucherCodeResource\Pages\ViewVoucherCode;
use App\Filament\Resources\VoucherCodeResource\Pages\EditVoucherCode;
use App\Filament\Resources\VoucherCodeResource\Pages;
use App\Filament\Resources\VoucherCodeResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelCommerinity\Models\VoucherCode;

class VoucherCodeResource extends Resource
{
    protected static ?string $model = VoucherCode::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string | \UnitEnum | null $navigationGroup = 'Shop';
    protected static ?string $slug = 'coupon';
    protected static ?string $pluralLabel = 'coupons';
    protected static ?string $navigationLabel = 'Coupons';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('code')
                    ->required()
                    ->hint(__('Max: 250'))
                    ->columnSpanFull()
                    ->maxLength(250),

                Fieldset::make('Voucher Timeline & Usage')
                    ->schema([
                        DateTimePicker::make('starts_from')->required()->placeholder('Set Start Date And Time'),
                        DateTimePicker::make('ends_till')->required()->placeholder('Set End Date And Time'),
                        TextInput::make('usage_per_customer')
                            ->label('Usage Per Customer')
                            ->required(),
                        TextInput::make('coupon_usage_limit')
                            ->label('Coupon Usage Limit')
                            ->required(),
                    ])->columns(2),


                Select::make('voucher_id')
                    ->relationship('voucher', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('coupon_usage_limit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('usage_per_user')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('times_used')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('starts_from')
                    ->date()
                    ->sortable(),
                TextColumn::make('ends_till')
                    ->date()
                    ->sortable(),
                TextColumn::make('voucher.name')
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
            'index' => ListVoucherCodes::route('/'),
            'create' => CreateVoucherCode::route('/create'),
            'view' => ViewVoucherCode::route('/{record}'),
            'edit' => EditVoucherCode::route('/{record}/edit'),
        ];
    }
}
