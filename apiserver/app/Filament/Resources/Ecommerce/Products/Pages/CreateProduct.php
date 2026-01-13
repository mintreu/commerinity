<?php

declare(strict_types=1);

namespace App\Filament\Resources\Ecommerce\Products\Pages;

use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
use App\Filament\Resources\Ecommerce\Products\ProductResource;
use App\Models\Ecommerce\FilterGroup;
use App\Services\Ecommerce\ProductManager;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Events\RecordCreated;
use Filament\Resources\Events\RecordSaved;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Throwable;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
    protected array $filterGroupCache = [];

//    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
//    {
//        // Build filter_options from the form data
//        $filterOptions = $this->buildFilterOptions($data);
//
//        // Prepare data for ProductManager
//        $productData = [
//            'name' => $data['name'],
//            'sku' => $data['sku'],
//            'url' => $data['url'],
//            'status' => $data['status'] ?? ProductStatusCast::DRAFT->value,
//            'type' => $data['type'] ?? ProductTypeCast::SIMPLE->value,
//            'filter_group_id' => $data['filter_group_id'] ?? null,
//            'category_id' => $data['category_id'] ?? null,
//            'price' => $data['price'] ?? 0,
//            'description' => $data['description'] ?? null,
//            'short_description' => $data['short_description'] ?? null,
//            'is_returnable' => $data['is_returnable'] ?? false,
//            'return_days' => $data['return_days'] ?? 0,
//            'filter_options' => $filterOptions,
//        ];
//
//        // Use ProductManager to create product (handles variants automatically)
//        $product = ProductManager::create($productData);
//
//        if (! $product) {
//            Notification::make()
//                ->title('Failed to create product')
//                ->danger()
//                ->send();
//
//            throw new \Exception('ProductManager::create() returned null');
//        }
//
//        Notification::make()
//            ->title('Product created')
//            ->success()
//            ->send();
//
//        return $product;
//    }
//
//    /**
//     * Build filter_options array from form data
//     * Form sends: filter_options[filter_id][option_id] = true
//     */
//    protected function buildFilterOptions(array $data): array
//    {
//        if (! isset($data['filter_options']) || ! is_array($data['filter_options'])) {
//            return [];
//        }
//
//        $filterOptions = [];
//
//        foreach ($data['filter_options'] as $filterId => $options) {
//            if (is_array($options)) {
//                // Collect selected option IDs
//                $selectedOptions = [];
//                foreach ($options as $optionId => $isSelected) {
//                    if ($isSelected) {
//                        $selectedOptions[] = (string) $optionId;
//                    }
//                }
//                if (! empty($selectedOptions)) {
//                    $filterOptions[(string) $filterId] = $selectedOptions;
//                }
//            }
//        }
//
//        return $filterOptions;
//    }





    public function form(Schema $schema): Schema
    {
        return parent::form($schema)
            ->schema([

                Section::make('Detail')
                    ->aside()
                    ->columnSpanFull()
                    ->schema([
                        Select::make('type')
                            ->options(collect(ProductTypeCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                            ->live()
                            ->label('Product Type')
                            ->placeholder('Select product type...')
                            ->helperText('Choose how this product behaves (e.g. Simple, Configurable).')
                            ->required(),

                        TextInput::make('name')
                            ->required()
                            ->columnSpanFull()
                            ->lazy()
                            ->afterStateUpdated(fn($state,Set $set) => $set('url',Str::slug($state)))
                            ->placeholder('Unnamed Product'),

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





                Section::make('Filter Configuration')
                    ->description('Attach this product to a filter group for storefront browsing and search.')
                    ->aside()
                    ->columnSpanFull()
                    ->live()
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



            ]);
    }





    public function getFilterSelectionSchema(callable $get): array
    {
        $filterGroupId = $get('filter_group_id');
        $productType = $get('type');

        if (!$filterGroupId) {
            return [
                TextEntry::make('NoFilters')
                    ->state('Select a filter group first.'),
            ];
        }

        // Use cached group if exists
        $filterGroup = $this->filterGroupCache[$filterGroupId]
            ??= FilterGroup::with(['filters.options'])->find($filterGroupId);


        if (!$filterGroup || $filterGroup->filters->isEmpty()) {
            return [
                TextEntry::make('NoFiltersAvailable')
                    ->state('No filters available for this group.'),
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
                            ->live()
                            ->required($filter->is_required)
                        )
                        ->toArray()
                ),
        ];
    }







    protected function handleRecordCreation(array $data): Model
    {
        //$record = new ($this->getModel())($data);
        $record = ProductManager::create($data);

        if ($parentRecord = $this->getParentRecord()) {
            return $this->associateRecordWithParent($record, $parentRecord);
        }

        $record->save();

        return $record;
    }


    /**
     * @param int|null $filterGroupId
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
