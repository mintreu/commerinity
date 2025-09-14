<?php

namespace App\Filament\Resources\HelpDeskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConversationsRelationManager extends RelationManager
{
    protected static string $relationship = 'conversations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
//                Forms\Components\TextInput::make('message')
//                    ->required()
//                    ->maxLength(255),


                Forms\Components\SpatieMediaLibraryFileUpload::make('attachment')
                    ->label('Send Attachment')
                    ->image()
                    ->collection('ticketConversationAttachment')

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message')
            ->columns([
                Tables\Columns\TextColumn::make('message'),
                Tables\Columns\TextColumn::make('author.name'),
                Tables\Columns\TextColumn::make('author.email'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
