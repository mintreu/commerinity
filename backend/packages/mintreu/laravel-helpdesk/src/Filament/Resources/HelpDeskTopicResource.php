<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\Pages\ListHelpDeskTopics;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\Pages\CreateHelpDeskTopic;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\Pages\ViewHelpDeskTopic;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\Pages\EditHelpDeskTopic;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\Pages;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\RelationManagers;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelHelpdesk\Models\HelpDeskTopic;

class HelpDeskTopicResource extends Resource
{
    protected static ?string $model = HelpDeskTopic::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'HelpDesk & Support';
    protected static ?string $recordRouteKeyName = 'slug';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                IconColumn::make('ticketable')->default(false)->boolean()
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
            'index' => ListHelpDeskTopics::route('/'),
            'create' => CreateHelpDeskTopic::route('/create'),
            'view' => ViewHelpDeskTopic::route('/{record:slug}'),
            'edit' => EditHelpDeskTopic::route('/{record:slug}/edit'),
        ];
    }
}
