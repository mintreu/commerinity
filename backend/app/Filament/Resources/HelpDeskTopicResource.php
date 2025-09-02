<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HelpDeskTopicResource\Pages;
use App\Filament\Resources\HelpDeskTopicResource\RelationManagers;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelHelpdesk\Models\HelpDeskTopic;

class HelpDeskTopicResource extends Resource
{
    protected static ?string $model = HelpDeskTopic::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'HelpDesk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\IconColumn::make('ticketable')->default(false)->boolean()
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
            'index' => Pages\ListHelpDeskTopics::route('/'),
            'create' => Pages\CreateHelpDeskTopic::route('/create'),
            'view' => Pages\ViewHelpDeskTopic::route('/{record}'),
            'edit' => Pages\EditHelpDeskTopic::route('/{record}/edit'),
        ];
    }
}
