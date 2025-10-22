<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages\ListHelpDesks;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages\CreateHelpDesk;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages\ViewHelpDesk;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages\EditHelpDesk;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskResource\Pages\ManageTicketConversations;
use App\Filament\Resources\HelpDeskResource\Pages;
use App\Filament\Resources\HelpDeskResource\RelationManagers;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelHelpdesk\Casts\HelpdeskPriorityCast;
use Mintreu\LaravelHelpdesk\Casts\HelpdeskStatusCast;
use Mintreu\LaravelHelpdesk\Models\HelpDesk;

class HelpDeskResource extends Resource
{
    protected static ?string $model = HelpDesk::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'HelpDesk & Support';
    protected static ?string $recordRouteKeyName = 'uuid';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

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
                TextColumn::make('topic.name')->badge(),
                TextColumn::make('title')->limit(50),
                TextColumn::make('authorable.name')->label('Author')->badge(),
                TextColumn::make('priority')->badge(),
                TextColumn::make('status')->badge(),
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
           // RelationManagers\ConversationsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHelpDesks::route('/'),
            'create' => CreateHelpDesk::route('/create'),
            'view' => ViewHelpDesk::route('/{record:uuid}'),
            'edit' => EditHelpDesk::route('/{record:uuid}/edit'),
            'conversation' => ManageTicketConversations::route('/{record:uuid}/conversation'),
        ];
    }
}
