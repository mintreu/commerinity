<?php

namespace App\Filament\Resources\Ecommerce\Vouchers\RelationManagers;

use App\Models\Ecommerce\VoucherCode;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class VoucherCodesRelationManager extends RelationManager
{
    protected static string $relationship = 'codes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->maxLength(32)
                    ->helperText('Leave empty to auto-generate'),
                Toggle::make('is_primary')
                    ->label('Primary Code')
                    ->default(false),
                Select::make('type')
                    ->options([
                        VoucherCode::TYPE_PUBLIC => 'Public',
                        VoucherCode::TYPE_PRIVATE => 'Private',
                        VoucherCode::TYPE_SINGLE_USE => 'Single Use',
                    ])
                    ->default(VoucherCode::TYPE_PUBLIC)
                    ->required(),
                TextInput::make('coupon_usage_limit')
                    ->numeric()
                    ->default(0),
                TextInput::make('usage_per_user')
                    ->numeric()
                    ->default(0),
                DatePicker::make('starts_from'),
                DatePicker::make('ends_till'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->copyable(),
                IconColumn::make('is_primary')
                    ->boolean(),
                TextColumn::make('type')
                    ->formatStateUsing(fn (int $state) => match ($state) {
                        VoucherCode::TYPE_PUBLIC => 'Public',
                        VoucherCode::TYPE_PRIVATE => 'Private',
                        VoucherCode::TYPE_SINGLE_USE => 'Single Use',
                        default => 'Unknown',
                    }),
                TextColumn::make('coupon_usage_limit')
                    ->numeric(),
                TextColumn::make('usage_per_user')
                    ->numeric(),
                TextColumn::make('times_used')
                    ->numeric(),
                TextColumn::make('starts_from')
                    ->date(),
                TextColumn::make('ends_till')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
