<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NaukriApplicationResource\Pages;
use App\Filament\Resources\NaukriApplicationResource\RelationManagers;
use App\Models\NaukriApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NaukriApplicationResource extends Resource
{
    protected static ?string $model = NaukriApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('guardian_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_paid')
                    ->required(),
                Forms\Components\TextInput::make('educations'),
                Forms\Components\TextInput::make('skills'),
                Forms\Components\TextInput::make('experiences'),
                Forms\Components\TextInput::make('reference_name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('reference_contact')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('submitted_at'),
                Forms\Components\Select::make('naukri_id')
                    ->relationship('naukri', 'name'),
                Forms\Components\Select::make('address_id')
                    ->relationship('address', 'title'),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->maxLength(255)
                    ->default('awaiting_payment'),
                Forms\Components\Textarea::make('status_feedback')
                    ->columnSpanFull(),


                Forms\Components\Section::make('Transaction')
                    ->relationship('transaction')
                    ->schema([
                        Forms\Components\TextInput::make('uuid'),
                        Forms\Components\TextInput::make('amount'),
                        Forms\Components\Radio::make('verified'),
                    ])


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guardian_name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean(),
                Tables\Columns\TextColumn::make('reference_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference_contact')
                    ->searchable(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('naukri.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('address.title')
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
            'index' => Pages\ListNaukriApplications::route('/'),
            'create' => Pages\CreateNaukriApplication::route('/create'),
            'view' => Pages\ViewNaukriApplication::route('/{record}'),
            'edit' => Pages\EditNaukriApplication::route('/{record}/edit'),
        ];
    }
}
