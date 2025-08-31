<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HelpDeskFaqResource\Pages;
use App\Filament\Resources\HelpDeskFaqResource\RelationManagers;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Mintreu\LaravelHelpdesk\Models\HelpDeskFaq;

class HelpDeskFaqResource extends Resource
{
    protected static ?string $model = HelpDeskFaq::class;

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
            'index' => Pages\ListHelpDeskFaqs::route('/'),
            'create' => Pages\CreateHelpDeskFaq::route('/create'),
            'view' => Pages\ViewHelpDeskFaq::route('/{record}'),
            'edit' => Pages\EditHelpDeskFaq::route('/{record}/edit'),
        ];
    }
}
