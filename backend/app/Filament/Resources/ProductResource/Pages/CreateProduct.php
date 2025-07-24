<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Casts\ProductTypeCast;
use App\Models\FilterGroup;
use App\Models\Product;
use App\Services\ProductService\ProductCreationService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CreateProduct extends CreateRecord
{
    use CreateRecord\Concerns\HasWizard;
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
        }catch (\Throwable $t){
            Notification::make()
                ->title('Error creating product')
                ->body($t->getMessage())
                ->danger()
                ->send();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Wizard::make([
                    Wizard\Step::make('Product Info')
                        ->icon('heroicon-s-folder')
                        ->schema(fn() => $this->getProductInfoFormSectionSchema()),
                    Wizard\Step::make('Filter Configuration')
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
            Forms\Components\Grid::make(3)
                ->schema([

                    // Product Type & Preview
                    Forms\Components\Grid::make(1)
                        ->columnSpan(1)
                        ->schema([
                            Forms\Components\Select::make('type')
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

                            Forms\Components\Placeholder::make('preview_type')
                                ->hiddenLabel()
                                ->disabled()
                                ->content(fn (Forms\Get $get) => filled($type = $get('type'))
                                    ? new HtmlString(
                                        '<div class="mt-2">
                                        <img src="' . ProductTypeCast::from($type)->getMedia() . '" alt="Product Type Preview" class="w-full h-48 object-cover rounded shadow" />
                                    </div>'
                                    )
                                    : null
                                )
                                ->visible(fn (Forms\Get $get) => filled($get('type')))
                        ]),

                    // Product Details Section
                    Forms\Components\Section::make('Basic Product Information')
                        ->description('Enter the primary information about this product.')
                        ->columnSpan(2)
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Product Name')
                                ->placeholder('e.g. Apple iPhone 15 Pro')
                                ->helperText('This is the name displayed to customers.')
                                ->required()
                                ->lazy()
                                ->maxLength(255)
                                ->afterStateUpdated(fn (Forms\Set $set, $state) => $set('url', Str::slug($state))),

                            Forms\Components\TextInput::make('url')
                                ->label('Product URL Slug')
                                ->placeholder('e.g. apple-iphone-15-pro')
                                ->helperText('Used in the product’s URL (e.g. /products/apple-iphone-15-pro).')
                                ->required()
                                ->unique('products', 'url', ignoreRecord: true)
                                ->maxLength(255),

                            Forms\Components\TextInput::make('sku')
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
            Forms\Components\Section::make('Filter Configuration')
                ->description('Attach this product to a filter group for storefront browsing and search.')
                ->aside()
                ->columnSpanFull()
                //->live()
                ->schema(fn(Forms\Get $get) => array_merge([
                    Forms\Components\Select::make('filter_group_id')
                        ->label('Filter Group')
                        ->placeholder('Select a filter group...')
                        ->helperText('Used for grouping and filtering products on the storefront.')
                        ->required()
                        ->live()
                        ->relationship('filterGroup', 'name')
                        ->searchable()
                        ->afterStateUpdated(fn ($state,Forms\Get $get) => $this->preloadFilterOptions($state, $get('type')))
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
                Forms\Components\Placeholder::make('NoFilters')
                    ->content('Select a filter group first.'),
            ];
        }

        // Use cached group if exists
        $filterGroup = $this->filterGroupCache[$filterGroupId]
            ??= FilterGroup::with(['filters.options'])->find($filterGroupId);


        if (!$filterGroup || $filterGroup->filters->isEmpty()) {
            return [
                Forms\Components\Placeholder::make('NoFiltersAvailable')
                    ->content('No filters available for this group.'),
            ];
        }

        $isConfigurable = $productType === 'configurable';


        return [
            Forms\Components\Section::make()
                //->aside()
                ->columns()
                ->heading('Product filters')
                ->description('All filters from the selected group must be configured.')
                ->schema(
                    $filterGroup->filters
                        ->map(fn($filter) => Forms\Components\Select::make("filter_options.{$filter->id}")
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
