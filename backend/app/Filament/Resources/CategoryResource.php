<?php

namespace App\Filament\Resources;

use App\Filament\Common\Schema\AdjacencySchema\HasAdjacencyFormSchema;
use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Resources\Resource;

class CategoryResource extends Resource
{
    use HasAdjacencyFormSchema;

    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Catalogue';

//    public static function form(Form $form): Form
//    {
//        return $form
//            ->schema([
//                Forms\Components\TextInput::make('name')
//                    ->required()
//                    ->maxLength(100),
//                Forms\Components\TextInput::make('url')
//                    ->maxLength(100),
//                Forms\Components\Toggle::make('status')
//                    ->required(),
//                Forms\Components\Toggle::make('is_visible_on_front')
//                    ->required(),
//                Forms\Components\TextInput::make('view_count')
//                    ->required()
//                    ->numeric()
//                    ->default(0),
//                Forms\Components\TextInput::make('order')
//                    ->numeric(),
//                Forms\Components\TextInput::make('meta_data'),
//                Forms\Components\Textarea::make('desc')
//                    ->columnSpanFull(),
//                Forms\Components\TextInput::make('parent_id')
//                    ->numeric(),
//            ]);
//    }
//
//    public static function table(Table $table): Table
//    {
//        return $table
//            ->columns([
//                Tables\Columns\TextColumn::make('name')
//                    ->searchable(),
//                Tables\Columns\TextColumn::make('url')
//                    ->searchable(),
//                Tables\Columns\IconColumn::make('status')
//                    ->boolean(),
//                Tables\Columns\IconColumn::make('is_visible_on_front')
//                    ->boolean(),
//                Tables\Columns\TextColumn::make('view_count')
//                    ->numeric()
//                    ->sortable(),
//                Tables\Columns\TextColumn::make('order')
//                    ->numeric()
//                    ->sortable(),
//                Tables\Columns\TextColumn::make('parent_id')
//                    ->numeric()
//                    ->sortable(),
//                Tables\Columns\TextColumn::make('created_at')
//                    ->dateTime()
//                    ->sortable()
//                    ->toggleable(isToggledHiddenByDefault: true),
//                Tables\Columns\TextColumn::make('updated_at')
//                    ->dateTime()
//                    ->sortable()
//                    ->toggleable(isToggledHiddenByDefault: true),
//            ])
//            ->filters([
//                //
//            ])
//            ->actions([
//                Tables\Actions\ViewAction::make(),
//                Tables\Actions\EditAction::make(),
//            ])
//            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
//                ]),
//            ]);
//    }


    public static function getForm(): array
    {
        return self::getAdjacencyResourceFormSchema();
    }

    public static function getParentForm(): array
    {
        return self::getAdjacencyResourceParentFormSchema();
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
