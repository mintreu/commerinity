<?php

namespace Mintreu\LaravelPenpress\Filament\Resources;

use Mintreu\LaravelPenpress\Filament\Resources\PageResource\Pages;
use Mintreu\LaravelPenpress\Filament\Resources\PageResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\HtmlString;
use Mintreu\LaravelPenpress\Models\Page;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Blogs & Pages';
    protected static ?string $recordRouteKeyName = 'url';
    protected static ?string $pluralLabel = 'WebPages';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Grid::make(3)
                    ->columnSpanFull()
                    ->schema([

                        Forms\Components\Grid::make(1)
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\Section::make('Page Detail')
                                    ->description('Tell about the page')
                                    ->columnSpanFull()
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->required()
                                            ->maxLength(255),
                                        TiptapEditor::make('content')
                                            ->columnSpanFull(),
                                    ]),


                                Forms\Components\Builder::make('sections')
                                    ->schema([
                                        Forms\Components\Builder\Block::make('card')
                                            ->schema([
                                                Forms\Components\TextInput::make('Title'),
                                                Forms\Components\Textarea::make('description')
                                            ]),

                                    ])->addable()->deletable()

                            ]),




                        Forms\Components\Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Section::make('Url Detail')
                                    ->collapsible()
                                    ->schema([
                                        Forms\Components\TextInput::make('slug')
                                            ->required()
                                            ->inlineLabel()
                                            ->lazy()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('prefix')
                                            ->lazy()
                                            ->inlineLabel()
                                            ->maxLength(255),

                                        Forms\Components\Placeholder::make('url_preview')
                                            ->live()
                                            ->label('Url : ')
                                            ->inlineLabel()
                                            ->visible(fn(Forms\Get $get) => $get('slug'))
                                            ->content(function (Forms\Get $get,Forms\Set $set){
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

                                        Forms\Components\TextInput::make('url')
                                            ->required()
                                            ->hidden()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                    ]),


                                Forms\Components\Section::make('Configuration')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Forms\Components\Toggle::make('status')
                                            ->required(),
                                        Forms\Components\TextInput::make('order')
                                            ->required()
                                            ->numeric()
                                            ->default(0),

                                        Forms\Components\TextInput::make('layout')
                                            ->required()
                                            ->maxLength(255)
                                            ->default('default'),
                                        Forms\Components\TextInput::make('template')
                                            ->maxLength(255),

                                    ]),


                                Forms\Components\Section::make('Header')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Forms\Components\KeyValue::make('meta')
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('custom_css')
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('custom_js')
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
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('prefix')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('layout')
                    ->searchable(),
                Tables\Columns\TextColumn::make('template')
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => \Mintreu\LaravelPenpress\Filament\Resources\PageResource\Pages\ListPages::route('/'),
            'create' => \Mintreu\LaravelPenpress\Filament\Resources\PageResource\Pages\CreatePage::route('/create'),
            'view' => \Mintreu\LaravelPenpress\Filament\Resources\PageResource\Pages\ViewPage::route('/{record:url}'),
            'edit' => \Mintreu\LaravelPenpress\Filament\Resources\PageResource\Pages\EditPage::route('/{record:url}/edit'),
        ];
    }
}
