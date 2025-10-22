<?php

namespace Mintreu\LaravelCategory\Support\AdjacencySchema;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Utilities\Get;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Saade\FilamentAdjacencyList\Forms\Components\AdjacencyList;

trait HasAdjacencyFormSchema
{
    public function getAdjacencyFormSchema(): array
    {
        return array_merge(
            self::$resource::getForm(),
            self::$resource::getParentForm(),
            [
                AdjacencyList::make('children')->columnSpanFull()
                    ->childrenKey('descendants')
                    ->form(self::$resource::getForm())
                    ->labelKey('url')
                    ->maxDepth(2)
                    ->addable()
                    ->editable()
                    ->deletable(),
            ]
        );
    }

    /**
     * This Part For Resource Methods
     * Required For Form.
     * Placed In Resource
     */
    public static function getAdjacencyResourceFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->lazy()
                ->afterStateUpdated(function (Set $set, $state) {
                    $set('url', Str::slug($state));
                })
                ->hint(__('Max: 100'))
                ->maxLength(100),

            TextInput::make('url')
                ->required()->unique(ignoreRecord: true)
                ->hint(__('Max: 150'))
                ->maxLength(150),

            Toggle::make('status')
                ->default(false)
                ->required(),

            SpatieMediaLibraryFileUpload::make('display')
                ->collection('displayImage'),
        ];
    }

    public static function getAdjacencyResourceParentFormSchema(): array
    {
        return [
            Toggle::make('show_parent')->label('Add/modify parent')->dehydrated(false)->live()
                ->helperText(fn (): HtmlString => new HtmlString('<ol class="font-semibold">
                        <li>Dont add parent for root levels.</li>
                        <li>Use the x icon to remove parent.</li>
                        </ol>')),

            SelectTree::make('parent_id')->searchable()->withCount()
                ->relationship('parent', 'url', 'parent_id')->visible(fn (Get $get): bool => $get('show_parent') ?? false),

        ];
    }
}
