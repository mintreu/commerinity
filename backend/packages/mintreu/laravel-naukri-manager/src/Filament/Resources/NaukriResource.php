<?php

namespace Mintreu\LaravelNaukriManager\Filament\Resources;

use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\Pages;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelNaukriManager\Models\Naukri;

class NaukriResource extends Resource
{
    protected static ?string $model = Naukri::class;
    protected static ?string $recordRouteKeyName = 'url';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Recruitment';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('role')
                    ->maxLength(255),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255),
                Forms\Components\TextInput::make('employment_type')
                    ->required()
                    ->maxLength(255)
                    ->default('internship'),
                Forms\Components\TextInput::make('vacancy')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\DatePicker::make('open_date'),
                Forms\Components\DatePicker::make('close_date'),
                Forms\Components\Toggle::make('is_payable')
                    ->required(),
                Forms\Components\TextInput::make('fees')
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('published'),
                Forms\Components\Textarea::make('status_feedback')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('employment_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vacancy')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('open_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('close_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_payable')
                    ->boolean(),
                Tables\Columns\TextColumn::make('fees')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
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
            'index' => \Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\Pages\ListNaukris::route('/'),
            'create' => \Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\Pages\CreateNaukri::route('/create'),
            'view' => \Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\Pages\ViewNaukri::route('/{record:url}'),
            'edit' => \Mintreu\LaravelNaukriManager\Filament\Resources\NaukriResource\Pages\EditNaukri::route('/{record:url}/edit'),
        ];
    }
}
