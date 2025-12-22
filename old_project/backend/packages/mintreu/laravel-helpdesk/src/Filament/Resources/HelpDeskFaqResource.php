<?php

namespace Mintreu\LaravelHelpdesk\Filament\Resources;

use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\Pages;
use Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\RelationManagers;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelHelpdesk\Models\HelpDeskFaq;

class HelpDeskFaqResource extends Resource
{
    protected static ?string $model = HelpDeskFaq::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'HelpDesk & Support';

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
                //
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
            'index' => \Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\Pages\ListHelpDeskFaqs::route('/'),
            'create' => \Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\Pages\CreateHelpDeskFaq::route('/create'),
            'view' => \Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\Pages\ViewHelpDeskFaq::route('/{record}'),
            'edit' => \Mintreu\LaravelHelpdesk\Filament\Resources\HelpDeskFaqResource\Pages\EditHelpDeskFaq::route('/{record}/edit'),
        ];
    }
}
