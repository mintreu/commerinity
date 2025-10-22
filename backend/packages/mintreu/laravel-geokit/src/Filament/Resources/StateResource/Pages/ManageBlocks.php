<?php

namespace Mintreu\LaravelGeokit\Filament\Resources\StateResource\Pages;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Mintreu\LaravelGeokit\Filament\Resources\StateResource;

class ManageBlocks extends ManageRelatedRecords
{
    protected static string $resource = StateResource::class;

    protected static string $relationship = 'blocks';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Blocks';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->lazy()
                    ->afterStateUpdated(fn ($state,Set $set) => $set('url',Str::slug($state)))
                    ->maxLength(255),


                TextInput::make('url')
                    ->required()
                    ->maxLength(255),


                TextInput::make('district_name')
                    ->required()
                    ->maxLength(255),

                Select::make('state_code')
                    ->relationship('state','name')


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('district_name')->label(__('District'))->sortable()->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
              //  Tables\Actions\AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
              //  Tables\Actions\DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
             //       Tables\Actions\DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
