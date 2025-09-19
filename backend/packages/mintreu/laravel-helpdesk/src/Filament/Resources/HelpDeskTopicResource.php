<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources;

use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\Pages;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\RelationManagers;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelHelpdesk\Models\HelpDeskTopic;

class HelpDeskTopicResource extends Resource
{
    protected static ?string $model = HelpDeskTopic::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'HelpDesk & Support';
    protected static ?string $recordRouteKeyName = 'slug';

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
            'index' => \Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\Pages\ListHelpDeskTopics::route('/'),
            'create' => \Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\Pages\CreateHelpDeskTopic::route('/create'),
            'view' => \Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\Pages\ViewHelpDeskTopic::route('/{record:slug}'),
            'edit' => \Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskTopicResource\Pages\EditHelpDeskTopic::route('/{record:slug}/edit'),
        ];
    }
}
