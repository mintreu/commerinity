<?php

namespace Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource\Pages;

use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Throwable;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Filament\Resources\ProductResource;
use Mintreu\LaravelProductCatalogue\Models\FilterGroup;
use Mintreu\LaravelProductCatalogue\Services\ProductCreationService;

class CreateProduct extends CreateRecord
{
    use HasWizard;
    protected static string $resource = ProductResource::class;
    protected static bool $canCreateAnother = false;
    public bool $continue = false;
    protected array $filterGroupCache = [];


    public function create(bool $another = false): void
    {
        $data = $this->form->getState();
        try {
            $this->record = ProductCreationService::make($data)->create();
            if ($this->record)
            {
                Notification::make()->success()->title('Product Created Successfully')->send();
                $this->redirect($this->getRedirectUrl());
            }
        }catch (Throwable $t){
            Notification::make()
                ->title('Error creating product')
                ->body($t->getMessage())
                ->danger()
                ->send();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Wizard::make([
                    Step::make('Product Info')
                        ->icon('heroicon-s-folder')
                        ->schema(fn() => $this->getProductInfoFormSectionSchema()),
                    Step::make('Filter Configuration')
                        ->icon('heroicon-c-cog-6-tooth')
                        ->schema(fn() => $this->getProductFilterSectionSchema()),

                ])->columnSpanFull()
                    //->skippable()
                    ->submitAction(new HtmlString(Blade::render(<<<BLADE
                        <x-filament::button type="submit" size="sm" color="success">
                            Create Product
                        </x-filament::button>
                    BLADE))),


            ]);
    }




















    public function getProductInfoFormSectionSchema(): array
    {
        return [
            Grid::make(3)
                ->schema([

                    // Product Type & Preview
                    Grid::make(1)
                        ->columnSpan(1)
                        ->schema([
                            Select::make('type')
                                ->label('Product Type')
                                ->placeholder('Select product type...')
                                ->helperText('Choose how this product behaves (e.g. Simple, Configurable).')
                                ->required()
                                ->live()
                                ->searchable()
                                ->preload()
                                ->options(
                                    collect(ProductTypeCast::cases())
                                        ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
                                        ->toArray()
                                ),

                            Placeholder::make('preview_type')
                                ->hiddenLabel()
                                ->disabled()
                                ->content(fn (Get $get) => filled($type = $get('type'))
                                    ? new HtmlString(
                                        '<div class="mt-2">
                                        <img src="' . ProductTypeCast::from($type)->getMedia() . '" alt="Product Type Preview" class="w-full h-48 object-cover rounded shadow" />
                                    </div>'
                                    )
                                    : null
                                )
                                ->visible(fn (Get $get) => filled($get('type')))
                        ]),

                    // Product Details Section
                    Section::make('Basic Product Information')
                        ->description('Enter the primary information about this product.')
                        ->columnSpan(2)
                        ->schema([
                            TextInput::make('name')
                                ->label('Product Name')
                                ->placeholder('e.g. Apple iPhone 15 Pro')
                                ->helperText('This is the name displayed to customers.')
                                ->required()
                                ->lazy()
                                ->maxLength(255)
                                ->afterStateUpdated(fn (Set $set, $state) => $set('url', Str::slug($state))),

                            TextInput::make('url')
                                ->label('Product URL Slug')
                                ->placeholder('e.g. apple-iphone-15-pro')
                                ->helperText('Used in the product’s URL (e.g. /products/apple-iphone-15-pro).')
                                ->required()
                                ->unique('products', 'url', ignoreRecord: true)
                                ->maxLength(255),

                            TextInput::make('sku')
                                ->label('SKU')
                                ->placeholder('e.g. IP15PRO-128GB')
                                ->helperText('Stock Keeping Unit — must be unique for inventory tracking.')
                                ->required()
                                ->unique('products', 'sku', ignoreRecord: true)
                                ->maxLength(255),



                        ]),
                ]),
        ];
    }



    public function getProductFilterSectionSchema(): array
    {
        return [
            Section::make('Filter Configuration')
                ->description('Attach this product to a filter group for storefront browsing and search.')
                ->aside()
                ->columnSpanFull()
                //->live()
                ->schema(fn(Get $get) => array_merge([
                    Select::make('filter_group_id')
                        ->label('Filter Group')
                        ->placeholder('Select a filter group...')
                        ->helperText('Used for grouping and filtering products on the storefront.')
                        ->required()
                        ->live()
                        ->relationship('filterGroup', 'name')
                        ->searchable()
                        ->afterStateUpdated(fn ($state,Get $get) => $this->preloadFilterOptions($state, $get('type')))
                        ->preload(),
                ],$this->getFilterSelectionSchema($get))),
        ];
    }


    // HELPER METHODS

    /**
     * @param callable $get
     * @return array
     */

    public function getFilterSelectionSchema(callable $get): array
    {
        $filterGroupId = $get('filter_group_id');
        $productType = $get('type');

        if (!$filterGroupId) {
            return [
                Placeholder::make('NoFilters')
                    ->content('Select a filter group first.'),
            ];
        }

        // Use cached group if exists
        $filterGroup = $this->filterGroupCache[$filterGroupId]
            ??= FilterGroup::with(['filters.options'])->find($filterGroupId);


        if (!$filterGroup || $filterGroup->filters->isEmpty()) {
            return [
                Placeholder::make('NoFiltersAvailable')
                    ->content('No filters available for this group.'),
            ];
        }

        $isConfigurable = $productType === 'configurable';


        return [
            Section::make()
                //->aside()
                ->columns()
                ->heading('Product filters')
                ->description('All filters from the selected group must be configured.')
                ->schema(
                    $filterGroup->filters
                        ->map(fn($filter) => Select::make("filter_options.{$filter->id}")
                            ->label($filter->name)
                            ->options($filter->options->pluck('value', 'id'))
                            ->multiple($isConfigurable)
                            ->preload()
//                            ->createOptionModalHeading('Create '.$filter->name.' Option')
//                            ->createOptionForm([
//                                Forms\Components\TextInput::make('value')->label('Option')
//                                    ->unique('filter_options','value'),
//                                Forms\Components\TextInput::make('swatch_value')->label('Display'),
//                            ])
//                            ->createOptionUsing(function (array $data) use ($filter,$filterGroup) {
//                                if ($data)
//                                {
//                                    $filter->options()->create($data);
//
//                                    // Invalidate the current filter group cache to force reload
//                                    unset($this->filterGroupCache[$filterGroup->id]);
//
//                                    // Re-run preload and reload dynamic filter fields
//                                    $this->preloadFilterOptions($filterGroup->id, $this->data['type']);
//
//                                    $this->dispatch('refreshForm'); // Custom Livewire event (optional, for UI)
//
//                                    Notification::make()
//                                        ->title('Filter Option Created Successfully')
//                                        ->success()
//                                        ->send();
//                                }
//                            })


                            ->live()
                            ->required($filter->is_required))
                        ->toArray()
                ),
        ];
    }


    /**
     * @param int $filterGroupId
     * @param string $productType
     * @return void
     */
    protected function preloadFilterOptions(?int $filterGroupId = null, string $productType): void
    {
       if ($filterGroupId)
       {
           // Pull from cache or query
           $filterGroup = $this->filterGroupCache[$filterGroupId]
               ??= FilterGroup::with('filters.options')->find($filterGroupId);

           if (! $filterGroup) return;

           $isConfigurable = $productType === 'configurable';

           $filterOptionState = [];

           foreach ($filterGroup->filters as $filter) {
               $fieldKey = "filter_options.{$filter->id}";

               // Fill with empty array if multiple, or null for single
               $filterOptionState[$fieldKey] = $isConfigurable ? [] : null;
           }

           // Inject into the form state
           $this->form->fill(array_merge($this->data,$filterOptionState));
       }
    }








}
