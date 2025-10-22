<?php

namespace Mintreu\LaravelNaukriManager\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\Pages\ListNaukris;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\Pages\CreateNaukri;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\Pages\ViewNaukri;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\Pages\EditNaukri;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\Pages;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelNaukriManager\Models\Naukri;

class NaukriResource extends Resource
{
    protected static ?string $model = Naukri::class;
    protected static ?string $recordRouteKeyName = 'url';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Recruitment';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('url')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('role')
                    ->maxLength(255),
                TextInput::make('location')
                    ->maxLength(255),
                TextInput::make('employment_type')
                    ->required()
                    ->maxLength(255)
                    ->default('internship'),
                TextInput::make('vacancy')
                    ->required()
                    ->numeric()
                    ->default(1),
                DatePicker::make('open_date'),
                DatePicker::make('close_date'),
                Toggle::make('is_payable')
                    ->required(),
                TextInput::make('fees')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('published'),
                Textarea::make('status_feedback')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('url')
                    ->searchable(),
                TextColumn::make('role')
                    ->searchable(),
                TextColumn::make('location')
                    ->searchable(),
                TextColumn::make('employment_type')
                    ->searchable(),
                TextColumn::make('vacancy')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('open_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('close_date')
                    ->date()
                    ->sortable(),
                IconColumn::make('is_payable')
                    ->boolean(),
                TextColumn::make('fees')
                    ->numeric()
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
            'index' => ListNaukris::route('/'),
            'create' => CreateNaukri::route('/create'),
            'view' => ViewNaukri::route('/{record:url}'),
            'edit' => EditNaukri::route('/{record:url}/edit'),
        ];
    }
}
