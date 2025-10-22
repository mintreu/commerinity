<?php

namespace Mintreu\LaravelNaukriManager\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Radio;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriApplicationResource\Pages\ListNaukriApplications;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriApplicationResource\Pages\CreateNaukriApplication;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriApplicationResource\Pages\ViewNaukriApplication;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriApplicationResource\Pages\EditNaukriApplication;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriApplicationResource\Pages;
use Mintreu\LaravelNaukriManager\Filament\Resources\NaukriApplicationResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelNaukriManager\Models\NaukriApplication;

class NaukriApplicationResource extends Resource
{
    protected static ?string $model = NaukriApplication::class;
    protected static ?string $recordRouteKeyName = 'uuid';
    protected static string | \UnitEnum | null $navigationGroup = 'Recruitment';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->maxLength(255),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('guardian_name')
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_paid')
                    ->required(),
                TextInput::make('educations'),
                TextInput::make('skills'),
                TextInput::make('experiences'),
                TextInput::make('reference_name')
                    ->maxLength(255),
                TextInput::make('reference_contact')
                    ->maxLength(255),
                DateTimePicker::make('submitted_at'),
                Select::make('naukri_id')
                    ->relationship('naukri', 'name'),
                Select::make('address_id')
                    ->relationship('address', 'title'),
                TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('awaiting_payment'),
                Textarea::make('status_feedback')
                    ->columnSpanFull(),


                Section::make('Transaction')
                    ->relationship('transaction')
                    ->schema([
                        TextInput::make('uuid'),
                        TextInput::make('amount'),
                        Radio::make('verified'),
                    ])


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('guardian_name')
                    ->searchable(),
                IconColumn::make('is_paid')
                    ->boolean(),
                TextColumn::make('reference_name')
                    ->searchable(),
                TextColumn::make('reference_contact')
                    ->searchable(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('naukri.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('address.title')
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
            'index' => ListNaukriApplications::route('/'),
            'create' => CreateNaukriApplication::route('/create'),
            'view' => ViewNaukriApplication::route('/{record:uuid}'),
            'edit' => EditNaukriApplication::route('/{record:uuid}/edit'),
        ];
    }
}
