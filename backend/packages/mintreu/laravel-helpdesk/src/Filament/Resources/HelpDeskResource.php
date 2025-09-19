<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources;

use App\Filament\Resources\HelpDeskResource\Pages;
use App\Filament\Resources\HelpDeskResource\RelationManagers;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelHelpdesk\Casts\HelpdeskPriorityCast;
use Mintreu\LaravelHelpdesk\Casts\HelpdeskStatusCast;
use Mintreu\LaravelHelpdesk\Models\HelpDesk;

class HelpDeskResource extends Resource
{
    protected static ?string $model = HelpDesk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'HelpDesk & Support';
    protected static ?string $recordRouteKeyName = 'uuid';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Ticket Details')
                    ->columns(3)
                    ->schema([
                        TextInput::make('title')->columnSpan(2),
                        Select::make('topic_id')
                            ->label('Topic')
                            ->relationship('topic','name'),
                        Textarea::make('description')->columnSpanFull(),
                        Select::make('priority')
                            ->options(collect(HelpdeskPriorityCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])),
                        Select::make('status')
                            ->options(collect(HelpdeskStatusCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])),


                        SpatieMediaLibraryFileUpload::make('screenshot')
                            ->multiple()
                            ->collection('ticketAttachment')

                    ]),





            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultGroup('status')
            ->defaultGroup('topic.name')
            ->modifyQueryUsing(fn($query) => $query->latest())
            ->columns([
                Tables\Columns\TextColumn::make('topic.name')->badge(),
                Tables\Columns\TextColumn::make('title')->limit(50),
                Tables\Columns\TextColumn::make('authorable.name')->label('Author')->badge(),
                Tables\Columns\TextColumn::make('priority')->badge(),
                Tables\Columns\TextColumn::make('status')->badge(),
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
           // RelationManagers\ConversationsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages\ListHelpDesks::route('/'),
            'create' => \Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages\CreateHelpDesk::route('/create'),
            'view' => \Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages\ViewHelpDesk::route('/{record:uuid}'),
            'edit' => \Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages\EditHelpDesk::route('/{record:uuid}/edit'),
            'conversation' => \Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages\ManageTicketConversations::route('/{record:uuid}/conversation'),
        ];
    }
}
