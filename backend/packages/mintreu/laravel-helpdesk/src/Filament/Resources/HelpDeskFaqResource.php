<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\Pages\ListHelpDeskFaqs;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\Pages\CreateHelpDeskFaq;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\Pages\ViewHelpDeskFaq;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\Pages\EditHelpDeskFaq;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\Pages;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\RelationManagers;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelHelpdesk\Models\HelpDeskFaq;

class HelpDeskFaqResource extends Resource
{
    protected static ?string $model = HelpDeskFaq::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'HelpDesk & Support';

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
                //
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
            'index' => ListHelpDeskFaqs::route('/'),
            'create' => CreateHelpDeskFaq::route('/create'),
            'view' => ViewHelpDeskFaq::route('/{record}'),
            'edit' => EditHelpDeskFaq::route('/{record}/edit'),
        ];
    }
}
