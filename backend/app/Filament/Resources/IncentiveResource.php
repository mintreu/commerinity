<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncentiveResource\Pages;
use App\Filament\Resources\IncentiveResource\RelationManagers;
use App\Models\Incentive;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IncentiveResource extends Resource
{
    protected static ?string $model = Incentive::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Incentive & Commission';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('transaction_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('incentivable_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('incentivable_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('sourceable_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('sourceable_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('depth')
                    ->numeric(),
                Forms\Components\TextInput::make('metadata'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('incentivable_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('incentivable_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sourceable_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sourceable_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('depth')
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
            'index' => Pages\ListIncentives::route('/'),
            'create' => Pages\CreateIncentive::route('/create'),
            'view' => Pages\ViewIncentive::route('/{record}'),
            'edit' => Pages\EditIncentive::route('/{record}/edit'),
        ];
    }
}
