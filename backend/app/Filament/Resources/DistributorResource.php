<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\DistributorResource\Pages\ListDistributors;
use App\Filament\Resources\DistributorResource\Pages\CreateDistributor;
use App\Filament\Resources\DistributorResource\Pages\ViewDistributor;
use App\Filament\Resources\DistributorResource\Pages\EditDistributor;
use App\Filament\Resources\DistributorResource\Pages;
use App\Filament\Resources\DistributorResource\RelationManagers;
use App\Models\Distributor;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DistributorResource extends Resource
{
    protected static ?string $model = Distributor::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->maxLength(36),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('mobile')
                    ->maxLength(255),
                DateTimePicker::make('mobile_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('draft'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('mobile')
                    ->searchable(),
                TextColumn::make('mobile_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
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
            'index' => ListDistributors::route('/'),
            'create' => CreateDistributor::route('/create'),
            'view' => ViewDistributor::route('/{record}'),
            'edit' => EditDistributor::route('/{record}/edit'),
        ];
    }
}
