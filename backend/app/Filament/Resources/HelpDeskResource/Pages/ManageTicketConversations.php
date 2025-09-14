<?php

namespace App\Filament\Resources\HelpDeskResource\Pages;

use App\Filament\Resources\HelpDeskResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ManageTicketConversations extends ManageRelatedRecords
{
    protected static string $resource = HelpDeskResource::class;

    protected static string $relationship = 'conversations';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Conversations';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),

                Forms\Components\SpatieMediaLibraryFileUpload::make('attachment')
                    ->label('Send Attachment')
                    ->image()
                    ->multiple()
                    ->maxFiles(10)
                    ->hint('Max File: 10 images')
                    ->columnSpanFull()
                    ->collection('ticketConversationAttachment')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message')
            ->columns([
                Tables\Columns\TextColumn::make('message'),
                Tables\Columns\TextColumn::make('authorable.name'),
                Tables\Columns\TextColumn::make('authorable_type')
                    ->badge()
                    ->formatStateUsing(fn($state) => Str::afterLast($state,'\\')),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('Send Message'))
                   ->mutateFormDataUsing(function (array $data){
                       $supportUser = filament()->auth()->user();
                       return array_merge($data,[
                           'authorable_type' => get_class($supportUser),
                           'authorable_id' => $supportUser->id,
                       ]);
                   }),
               // Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
//                Tables\Actions\EditAction::make(),
//                Tables\Actions\DissociateAction::make(),
//                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DissociateBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
