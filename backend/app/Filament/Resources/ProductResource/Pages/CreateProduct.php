<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Casts\ProductTypeCast;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
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

//    protected function getCreateFormAction(): Action
//    {
//        return $this->continue ?  Action::make('continue')
//            ->label(__('Continue'))
//            ->action(fn() => null) :
//            Action::make('create')
//                ->label(__('filament-panels::resources/pages/create-record.form.actions.create.label'))
//                ->submit('create')
//                ->color('success')
//                ->keyBindings(['mod+s'])
//            ;
//    }

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
                    ->skippable()
                    ->submitAction(new HtmlString(Blade::render(<<<BLADE
                        <x-filament::button type="submit" size="sm" color="success">
                            Create Product
                        </x-filament::button>
                    BLADE)))
                ,




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
                                ->default('Unnamed Product')
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

                            Forms\Components\Select::make('parent_id')
                                ->label('Parent Product (optional)')
                                ->relationship(
                                    name: 'parent',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn ($query, Forms\Get $get) => $query->where('type', $get('type'))
                                )
                                ->searchable()
                                ->helperText('If this product is a variant, select its main (parent) product.'),
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
                ->schema([
                    Forms\Components\Select::make('filter_group_id')
                        ->label('Filter Group')
                        ->placeholder('Select a filter group...')
                        ->helperText('Used for grouping and filtering products on the storefront.')
                        ->required()
                        ->relationship('filter_group', 'name')
                        ->searchable()
                        ->preload(),
                ]),
        ];
    }



}
