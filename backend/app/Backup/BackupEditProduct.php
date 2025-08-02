<?php

namespace App\Backup;

use App\Casts\ModelStatusCast;
use App\Filament\Resources\ProductResource;
use App\Models\FilterGroup;
use Filament\Actions;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Filament\Forms\Form;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Str;

class BackupEditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }


    public function getRelationManagers(): array
    {
        $relationManagers = [];
        if ($this->record->type == 'configurable') {
            $relationManagers[] = ProductResource\RelationManagers\VariantsRelationManager::class;
        }

        return $relationManagers;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        // Ensure base price is properly loaded as integer (matches database type)
        $data['price'] = $this->record->price ?? 0;
        $data['type'] = $this->record->type;
        $data['filter_group_id'] = $this->record->filter_group_id;



        // Handle filter options depending on product type
        if ($this->record->type === 'configurable' && !$this->record->parent_id) {
            // For configurable parent products, get filter options using the relationship
            $this->record->load('filterOptions.filter');

            // Group by filter ID and map to arrays of option IDs
            $data['filter_options'] = $this->record->filterOptions
                ->groupBy('filter_id')
                ->map(function ($options) {
                    return $options->pluck('id')->toArray();
                })
                ->toArray();
        } else {
            // For simple products or variants, load from the regular filterOptions relation
            $data['filter_options'] = $this->record->fresh(['filterOptions'])->filterOptions
                ->groupBy('filter_id')
                ->map(fn($options) => $options->pluck('id')->first())
                ->toArray();
        }

        return $data;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('Product')
                ->tabs([

                    Forms\Components\Tabs\Tab::make('Primary')
                        ->schema([
                            Forms\Components\Section::make()
                                ->aside()
                                ->heading('Primary information')
                                ->description('required for product page management')
                                ->columns(2)
                                ->schema([

                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                            if (($get('url') ?? '') !== Str::slug($old)) {
                                                return;
                                            }

                                            $set('url', Str::slug($state));

                                            // Also update SKU if it matches the old name pattern
                                            if (($get('sku') ?? '') !== strtoupper(Str::slug($old))) {
                                                return;
                                            }

                                            $set('sku', strtoupper(Str::slug($state)));
                                        }),

                                    Forms\Components\TextInput::make('sku')
                                        ->required()
                                        ->unique(ignoreRecord: true),

                                    Forms\Components\TextInput::make('url')
                                        ->label('URL Slug')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->helperText('URL-friendly version of the product name'),

//                                    SelectTree::make('category_id')
//                                        ->label('Category')
//                                        ->relationship(
//                                            'category',
//                                            'name',
//                                            'parent_id',
//                                            fn ($query) => $query
//                                                ->where('status', '=', true)
//                                                ->orderBy('name')
//                                        )
//                                        ->required()
//                                        ->searchable(),
                                ]),

                            Forms\Components\ToggleButtons::make('status')
                                ->label('Status')
                                ->inline()
                                ->options(ModelStatusCast::class)
                                ->default(ModelStatusCast::DRAFT->value)
                                ->required(),
                        ]),

                    Forms\Components\Tabs\Tab::make('About')
                        ->schema([
                            Forms\Components\Textarea::make('short_description')
                                ->label('Short Description')
                                ->required()
                                ->extraInputAttributes(['style' => 'min-height: 15rem;'])
                                ->columnSpanFull(),

                             TiptapEditor::make('description')
                                ->label('Long Description')
                                ->required()
                                ->extraInputAttributes(['style' => 'min-height: 30rem;'])
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Tabs\Tab::make('Media')
                        ->schema([
//                            CuratorPicker::make('product_display')
//                                ->label('Display Image')
//                                ->columnSpanFull()
//                                ->relationship('product_display', 'id'),
//                            CuratorPicker::make('product_gallery')
//                                ->label('Product Gallery')
//                                ->multiple()
//                                ->columnSpanFull()
//                                ->relationship('product_gallery', 'id'),
                        ]),



                    Forms\Components\Tabs\Tab::make('Filters')

                        ->schema([

                            Forms\Components\Section::make()
                                ->aside()->columns(2)
                                ->heading('Product filters')
                                ->description('All filters from the selected group must be configured.')
                                ->schema(function (Forms\Get $get) {
                                    $filterGroup = FilterGroup::find($get('filter_group_id'));
                                    if (!$filterGroup) {
                                        return [];
                                    }

                                    // For variant products, use parent's selected filter options
                                    if ($this->record->parent_id) {
                                        $parentProduct = $this->record->parent()->with('filterOptions.filter')->first();
                                        if ($parentProduct) {
                                            $availableOptions = $parentProduct->filterOptionsGrouped();

                                            return $filterGroup->filters->map(function ($filter) use ($availableOptions) {
                                                // If this filter has available options from the parent
                                                if (isset($availableOptions[$filter->id])) {
                                                    return Forms\Components\Select::make("filter_options.{$filter->id}")
                                                        ->label($filter->name)
                                                        ->options($availableOptions[$filter->id]['options']->pluck('value', 'id'))
                                                        ->required()
                                                        ->searchable()
                                                        ->preload();
                                                }

                                                // Fallback to all options if parent doesn't have selected options
                                                return Forms\Components\Select::make("filter_options.{$filter->id}")
                                                    ->label($filter->name)
                                                    ->options(
                                                        $filter->options->pluck('value', 'id')
                                                    )
                                                    ->required()
                                                    ->searchable()
                                                    ->preload();
                                            })->toArray();
                                        }
                                    }

                                    // For parent configurable products or if parent not found
                                    return $filterGroup->filters->map(function ($filter) {
                                        return Forms\Components\Select::make("filter_options.{$filter->id}")
                                            ->label($filter->name)
                                            ->options(
                                                $filter->options->pluck('value', 'id')
                                            )
                                            ->multiple($this->record->type === 'configurable')
                                            ->required()
                                            ->searchable()
                                            ->preload();
                                    })->toArray();
                                }),
                        ]),
                ])
                ->persistTabInQueryString()
                ->contained(false)
                ->columnSpanFull(),
        ]);
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $data = $this->form->getState();

        // Extract bulk pricing data
        $bulkPricing = $data['bulk_pricing'] ?? [];
        unset($data['bulk_pricing']);

        // Extract filter options data before removing from main data array
        $filterOptions = $data['filter_options'] ?? [];

        // Ensure type is preserved
        if (!isset($data['type'])) {
            $data['type'] = $this->record->type;
        }

        // Ensure filter_group_id is preserved
        if (!isset($data['filter_group_id'])) {
            $data['filter_group_id'] = $this->record->filter_group_id;
        }

        // Ensure price is properly set
        $data['price'] = $data['price'] ?? 0;

        // Update the form state without bulk pricing
        $this->form->fill($data);

        // Call parent save method
        parent::save($shouldRedirect, $shouldSendSavedNotification);

        // Delete existing bulk pricing records
        $this->record->bulkPricing()->delete();

        // Create new bulk pricing records
        if (!empty($bulkPricing)) {
            foreach ($bulkPricing as $pricing) {
                $this->record->bulkPricing()->create([
                    'quantity' => $pricing['quantity'],
                    'price' => $pricing['price'],
                ]);
            }
        }

        // Handle filter options for all products
        if (!empty($filterOptions)) {
            // Detach existing filter options
            $this->record->filterOptions()->detach();

            // Attach new filter options with proper pivot data
            foreach ($filterOptions as $filterId => $optionIds) {
                $optionIds = is_array($optionIds) ? $optionIds : [$optionIds];

                foreach ($optionIds as $optionId) {
                    $this->record->filterOptions()->attach($optionId, ['filter_id' => $filterId]);
                }
            }
        }

        // Reload the record to get fresh data
        $this->record->refresh();

        // Reload the form with fresh data including bulk pricing
        $this->form->fill([
            ...$data,
            'price' => $this->record->price,
            'type' => $this->record->type,
            'filter_group_id' => $this->record->filter_group_id,
            'bulk_pricing' => $this->record->bulkPricing()->get()->map(function ($pricing) {
                return [
                    'quantity' => $pricing->quantity,
                    'price' => $pricing->price,
                ];
            })->toArray(),
            'filter_options' => $filterOptions,
        ]);

        // If not redirecting, ensure the form is properly reloaded
        if (!$shouldRedirect) {
            $this->fillForm();
        }
    }




}
