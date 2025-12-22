<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\Schemas;

use App\Casts\GstTaxCast;
use Awcodes\Shout\Components\Shout;
use CodeWithDennis\FilamentSelectTree\SelectTree;
use Filament\Forms\Get;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\HtmlString;
use Mintreu\LaravelMoney\Filament\Forms\Components\MoneyInput;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Filament\Forms;
use Mintreu\LaravelProductCatalogue\Models\FilterGroup;
use Mintreu\LaravelProductCatalogue\Models\ProductTier;


class ProductEditFormSchema
{



    public static function config(): ?array
    {
        $instance = new static();
        return $instance->getSchema();
    }


    public function getSchema():array
    {
            return [
                Forms\Components\Tabs::make('Heading')
                    ->columnSpanFull()
                    ->contained(false)
                    ->tabs([
                        $this->getBasicInfoTab(),
                        $this->getPricingTab(),
                        $this->getMediaCollectionTab(),
                        $this->getConfigurationTab(),

                        Forms\Components\Tabs\Tab::make('Shipping')
                            ->columns()
                            ->schema([
                                // Weight, dimensions, shipping class, free shipping flag
                                Forms\Components\TextInput::make('width')
                                    ->numeric(),
                                Forms\Components\TextInput::make('height')
                                    ->numeric(),
                                Forms\Components\TextInput::make('length')
                                    ->numeric(),
                                Forms\Components\TextInput::make('weight')
                                    ->numeric(),

                            ]),

                    ]),
            ];
    }



    public function getBasicInfoTab()
    {
        return Forms\Components\Tabs\Tab::make('General')
            ->columns()
            ->schema([
                // Basic product info: name, slug, type, status
                Forms\Components\Section::make('Basic Info')
                    ->columns()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->columnSpanFull()
                            ->maxLength(255)
                            ->default('Unnamed Product'),

                        Forms\Components\TextInput::make('url')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('type')
                            ->required()
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Short Description')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        Forms\Components\Textarea::make('short_description')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Description')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        TiptapEditor::make('description')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),

            ]);
    }


    public function getPricingTab()
    {
        return Forms\Components\Tabs\Tab::make('Pricing')
            ->columns()
            ->schema([
                // Price, special price, tax class, cost, discount logic

                Forms\Components\Section::make('Pricing Breakdown')
                    ->aside()
                    ->columns()
                    ->schema([

                        Forms\Components\Select::make('stock_price')
                            ->prefix(LaravelMoney::defaultCurrency())
                            ->options(function ($record){
                                return ProductTier::where('product_id',$record->id)
                                    ->pluck('price','id')
                                    ->map(fn($price) => LaravelMoney::format($price));
                            })
                            ->live()
                            ->afterStateUpdated(function ($state,Forms\Set $set,$record){
                                $tire = ProductTier::find($state);
                                if ($tire)
                                {
                                   // $set('reward_point',$record->calculateRewardPoint($tire));
                                }
                            }),

                        Forms\Components\Select::make('tax_slab')
                            ->options(collect(GstTaxCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getFullLabel()])->toArray())
                            ->required(),

                        Forms\Components\Toggle::make('is_tax_inclusive')->default(false),
                        Forms\Components\Toggle::make('is_exempted')->default(false),


//                        Forms\Components\TextInput::make('reward_point')
//                            ->required()
//                            ->numeric()
//                            ->default(function ($record) {
//                                if ($record) {
//                                    $cheapestTier = $record->cheapestTier;
//                                    return $cheapestTier ? $record->calculateRewardPoint($cheapestTier) : 0;
//                                }
//                                return 0;
//                            }),

                        MoneyInput::make('price')
                            ->label('Price')
                            ->placeholder('Enter Price (e.g., 10025)')
                            ->prefix(LaravelMoney::defaultCurrency())
                            ->helperText('Enter amount in paisa. Decimals allowed.'),

                    ]),


                Forms\Components\Section::make('Applicable Range For Order')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('min_quantity')
                            ->required()
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('max_quantity')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ])


            ]);
    }




    public function getMediaCollectionTab()
    {
        return Forms\Components\Tabs\Tab::make('Media')
            ->schema([
                // Images, gallery, video
                Forms\Components\SpatieMediaLibraryFileUpload::make('display')
                    ->collection('displayImage'),

                Forms\Components\SpatieMediaLibraryFileUpload::make('banner')
                    ->multiple()
                    ->collection('bannerImage'),
            ]);
    }

    public function getConfigurationTab()
    {
        return Forms\Components\Tabs\Tab::make('Configuration')
            ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\Select::make('filter_group_id')
                            ->label(__('Filter Group'))
                            ->relationship('filterGroup','name')
                            ->live()
                            ->required(),


                        Shout::make('Caution')
                            ->color('danger')
                            ->visible(fn(Get $get,$record) => $get('filter_group_id') != $record->filter_group_id)
                            ->content(new HtmlString(
                                '<strong>Warning:</strong> Changing the filter group will <span style="text-decoration: underline;">permanently delete</span> all existing variants and create new ones based on the selected options.'
                            )),

                    ])
                    ->columnSpanFull(),

                Forms\Components\Fieldset::make('Options')
                    ->label(fn($record) => $record->type == ProductTypeCast::CONFIGURABLE ? 'Options' : 'Filter Options')
                    ->columns(2)
                    ->schema(fn(Get $get) => $this->getFilterSchema($get('filter_group_id'))),


                SelectTree::make('categories')
                    ->lazy()
                    ->relationship('categories', 'name', 'parent_id', function ($query, Get $get) {
                        return $query->where('status', true);
                    }),



                Forms\Components\KeyValue::make('meta_data')
                    ->label(fn () => new HtmlString('Meta Data (SEO)'))
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->addActionLabel('Add Meta Item')
                    ->reorderable()
                    ->keyPlaceholder('Enter key...')
                    ->valuePlaceholder('Enter value...')
                    ->helperText('You can define key-value pairs for metadata.')
                    ->required(false)
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'p-2',
                    ])
                    ->addable(true)
                    ->deletable(true)
                    ->reorderable(true)
                    ->default([])
                    ->columnSpanFull(),




            ]);
    }







    // Support


    protected function getFilterSchema(?int $filterGroupId = null): array
    {

        return !is_null($filterGroupId) ?  $this->getFilterDetails($filterGroupId) : [];
    }

    private function getFilterDetails(?int $filterGroupId = null): array
    {
        $filterGroup = FilterGroup::where('id', $filterGroupId)
            ->with('filters.options')
            ->get();

        return $filterGroup->flatMap(function ($group) {
            return $group->filters->map(function ($item) {
                $optionBag = $item->options->mapWithKeys(function ($item) {
                    return [$item['id'] => $item['value']];
                })->toArray();
                return Forms\Components\Select::make('filter_options.' . $item->id)
                    ->label($item->name)
                    ->options($optionBag)
                    ->required($item->is_required)
//                    ->multiple(fn() => $this->record->type == ProductTypeCast::CONFIGURABLE)
                    ->multiple(fn($record) => $record->type == ProductTypeCast::CONFIGURABLE)
                    ->default(3);
            });
        })->toArray();
    }











}
