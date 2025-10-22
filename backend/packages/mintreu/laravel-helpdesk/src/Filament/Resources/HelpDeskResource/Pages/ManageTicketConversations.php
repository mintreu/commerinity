<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource;

class ManageTicketConversations extends ManageRelatedRecords
{
    protected static string $resource = HelpDeskResource::class;

    protected static string $relationship = 'conversations';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Conversations';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('message')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(255),

                SpatieMediaLibraryFileUpload::make('attachment')
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
                TextColumn::make('message'),
                TextColumn::make('authorable.name'),
                TextColumn::make('authorable_type')
                    ->badge()
                    ->formatStateUsing(fn($state) => Str::afterLast($state,'\\')),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('Send Message'))
                   ->mutateDataUsing(function (array $data){
                       $supportUser = filament()->auth()->user();
                       return array_merge($data,[
                           'authorable_type' => get_class($supportUser),
                           'authorable_id' => $supportUser->id,
                       ]);
                   }),
               // Tables\Actions\AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
//                Tables\Actions\EditAction::make(),
//                Tables\Actions\DissociateAction::make(),
//                Tables\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
