<?php

namespace Mintreu\LaravelPenpress\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Mintreu\LaravelPenpress\Filament\Resources\PageResource\Pages\ListPages;
use Mintreu\LaravelPenpress\Filament\Resources\PageResource\Pages\CreatePage;
use Mintreu\LaravelPenpress\Filament\Resources\PageResource\Pages\ViewPage;
use Mintreu\LaravelPenpress\Filament\Resources\PageResource\Pages\EditPage;
use Mintreu\LaravelPenpress\Filament\Resources\PageResource\Pages;
use Mintreu\LaravelPenpress\Filament\Resources\PageResource\RelationManagers;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\HtmlString;
use Mintreu\LaravelPenpress\Models\Page;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Blogs & Pages';
    protected static ?string $recordRouteKeyName = 'url';
    protected static ?string $pluralLabel = 'WebPages';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([

                        Grid::make(1)
                            ->columnSpan(2)
                            ->schema([
                                Section::make('Page Detail')
                                    ->description('Tell about the page')
                                    ->columnSpanFull()
                                    ->schema([
                                        TextInput::make('title')
                                            ->required()
                                            ->maxLength(255),
                                        TiptapEditor::make('content')
                                            ->columnSpanFull(),
                                    ]),


                                Builder::make('sections')
                                    ->schema([
                                        Block::make('card')
                                            ->schema([
                                                TextInput::make('Title'),
                                                Textarea::make('description')
                                            ]),

                                    ])->addable()->deletable()

                            ]),




                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make('Url Detail')
                                    ->collapsible()
                                    ->schema([
                                        TextInput::make('slug')
                                            ->required()
                                            ->inlineLabel()
                                            ->lazy()
                                            ->maxLength(255),
                                        TextInput::make('prefix')
                                            ->lazy()
                                            ->inlineLabel()
                                            ->maxLength(255),

                                        Placeholder::make('url_preview')
                                            ->live()
                                            ->label('Url : ')
                                            ->inlineLabel()
                                            ->visible(fn(Get $get) => $get('slug'))
                                            ->content(function (Get $get,Set $set){
                                                $prefix = $get('prefix');
                                                $slug = $get('slug');
                                                $link = null;
                                                if ($slug)
                                                {
                                                    $link = config('app.client_url').'/'.$slug;
                                                }

                                                if ($prefix)
                                                {
                                                    $link = $link.'/'.$prefix;
                                                }

                                                if ($link)
                                                {
                                                    $set('url',$link);
                                                }

                                                return new HtmlString('
                                                    <a target="_blank" class="underline text-mute" href="'.$link.'" >'.$link.'</a>
                                                ');
                                            }),

                                        TextInput::make('url')
                                            ->required()
                                            ->hidden()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ]),


                                Section::make('Configuration')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Toggle::make('status')
                                            ->required(),
                                        TextInput::make('order')
                                            ->required()
                                            ->numeric()
                                            ->default(0),

                                        TextInput::make('layout')
                                            ->required()
                                            ->maxLength(255)
                                            ->default('default'),
                                        TextInput::make('template')
                                            ->maxLength(255),

                                    ]),


                                Section::make('Header')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        KeyValue::make('meta')
                                            ->columnSpanFull(),
                                        Textarea::make('custom_css')
                                            ->columnSpanFull(),
                                        Textarea::make('custom_js')
                                            ->columnSpanFull(),
                                    ]),

                            ]),

                    ]),





            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('prefix')
                    ->searchable(),
                TextColumn::make('url')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('layout')
                    ->searchable(),
                TextColumn::make('template')
                    ->searchable(),
                IconColumn::make('status')
                    ->boolean(),
                TextColumn::make('order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'view' => ViewPage::route('/{record:url}'),
            'edit' => EditPage::route('/{record:url}/edit'),
        ];
    }
}
