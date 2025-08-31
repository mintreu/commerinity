<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HelpDeskResource\Pages;
use App\Filament\Resources\HelpDeskResource\RelationManagers;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelHelpdesk\Models\HelpDesk;

class HelpDeskResource extends Resource
{
    protected static ?string $model = HelpDesk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
            'index' => Pages\ListHelpDesks::route('/'),
            'create' => Pages\CreateHelpDesk::route('/create'),
            'view' => Pages\ViewHelpDesk::route('/{record}'),
            'edit' => Pages\EditHelpDesk::route('/{record}/edit'),
        ];
    }
}
