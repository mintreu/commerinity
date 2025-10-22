<?php

namespace App\Filament\Resources\Incentives;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Incentives\Pages\ListIncentives;
use App\Filament\Resources\Incentives\Pages\CreateIncentive;
use App\Filament\Resources\Incentives\Pages\ViewIncentive;
use App\Filament\Resources\Incentives\Pages\EditIncentive;
use App\Filament\Resources\IncentiveResource\Pages;
use App\Filament\Resources\IncentiveResource\RelationManagers;
use App\Models\Incentive;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IncentiveResource extends Resource
{
    protected static ?string $model = Incentive::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Incentive & Commission';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('transaction_id')
                    ->required()
                    ->numeric(),
                TextInput::make('incentivable_type')
                    ->required()
                    ->maxLength(255),
                TextInput::make('incentivable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('sourceable_type')
                    ->required()
                    ->maxLength(255),
                TextInput::make('sourceable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('type')
                    ->required()
                    ->maxLength(255),
                TextInput::make('depth')
                    ->numeric(),
                TextInput::make('metadata'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('incentivable_type')
                    ->searchable(),
                TextColumn::make('incentivable_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sourceable_type')
                    ->searchable(),
                TextColumn::make('sourceable_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('depth')
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
            'index' => ListIncentives::route('/'),
            'create' => CreateIncentive::route('/create'),
            'view' => ViewIncentive::route('/{record}'),
            'edit' => EditIncentive::route('/{record}/edit'),
        ];
    }
}
