<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherCodeResource\Pages;
use App\Filament\Resources\VoucherCodeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mintreu\LaravelCommerinity\Models\VoucherCode;

class VoucherCodeResource extends Resource
{
    protected static ?string $model = VoucherCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Shop';
    protected static ?string $slug = 'coupon';
    protected static ?string $pluralLabel = 'coupons';
    protected static ?string $navigationLabel = 'Coupons';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('code')
                    ->required()
                    ->hint(__('Max: 250'))
                    ->columnSpanFull()
                    ->maxLength(250),

                Forms\Components\Fieldset::make('Voucher Timeline & Usage')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_from')->required()->placeholder('Set Start Date And Time'),
                        Forms\Components\DateTimePicker::make('ends_till')->required()->placeholder('Set End Date And Time'),
                        Forms\Components\TextInput::make('usage_per_customer')
                            ->label('Usage Per Customer')
                            ->required(),
                        Forms\Components\TextInput::make('coupon_usage_limit')
                            ->label('Coupon Usage Limit')
                            ->required(),
                    ])->columns(2),


                Forms\Components\Select::make('voucher_id')
                    ->relationship('voucher', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('coupon_usage_limit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('usage_per_user')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('times_used')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('starts_from')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_till')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('voucher.name')
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
            'index' => Pages\ListVoucherCodes::route('/'),
            'create' => Pages\CreateVoucherCode::route('/create'),
            'view' => Pages\ViewVoucherCode::route('/{record}'),
            'edit' => Pages\EditVoucherCode::route('/{record}/edit'),
        ];
    }
}
