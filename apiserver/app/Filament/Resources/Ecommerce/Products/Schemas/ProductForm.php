<?php

namespace App\Filament\Resources\Ecommerce\Products\Schemas;

use App\Casts\GstTaxCast;
use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
use App\Filament\Forms\Components\MoneyInput;
use App\Filament\Forms\Components\RichEditor\HeroBlock;
use App\Filament\Resources\Ecommerce\Products\Schemas\Traits\HasFilterConfiguration;
use App\Models\Ecommerce\Category;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    use HasFilterConfiguration;

    public static function configure(Schema $schema): Schema
    {
        return (new static())->build($schema);
    }

    protected function build(Schema $schema): Schema
    {
        return $schema->components($this->components());
    }

    protected function components(): array
    {
        return [
            Grid::make(3)->schema([
                $this->leftColumn(),
                $this->rightColumn(),
                $this->filterConfigurationFullWidth(),
            ])->columnSpanFull(),
        ];
    }

    protected function leftColumn(): Group
    {
        return Group::make()->schema([
            Section::make('General Information')->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                        if (($get('url') ?? '') !== Str::slug($old)) {
                            return;
                        }
                        $set('url', Str::slug($state));
                    }),

                TextInput::make('url')
                    ->label('Slug / URL Key')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Unique URL identifier for the product.'),

                Grid::make(3)->schema([
                    TextInput::make('sku')
                        ->label('SKU')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('Stock Keeping Unit'),

                    Select::make('type')
                        ->options(fn() => collect(ProductTypeCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                        ->required()
                        ->default(ProductTypeCast::SIMPLE->value)
                        ->live()
                        ->native(false),

                    Select::make('parent_id')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->placeholder('Select Parent (if variant)'),

                    MoneyInput::make('price')
                        ->label('Base Price')
                        ->required()
                        ->columnSpan(2)
                        ->helperText('Enter paise (e.g., 45000 = ₹450.00)'),

                    TextInput::make('hsn')->placeholder('HSN Code'),
                    Select::make('gst_tax_type')
                        ->options(collect(GstTaxCast::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()]))
                        ->required(),
                ]),
            ]),

            Section::make('Content')->schema([
                Textarea::make('short_description')
                    ->label('Short Summary')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),

                RichEditor::make('description')
                    ->label('Full Description')
                    ->columnSpanFull()
                    ->json()
                    ->customBlocks([
                        HeroBlock::class,
                    ])
                    ->fileAttachmentsDirectory('products/content'),
            ]),

            Section::make('Media')->schema([
                SpatieMediaLibraryFileUpload::make('display')
                    ->label('Thumbnail Image')
                    ->collection('displayImage')
                    ->image()
                    ->imageEditor()
                    ->helperText('Primary image for listings (500x500px suggested)'),

                SpatieMediaLibraryFileUpload::make('banner')
                    ->label('Gallery Images')
                    ->collection('bannerImage')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->imageEditor()
                    ->storeFileNamesIn('original_filename'),
            ])->collapsible()->collapsed(),
        ])->columnSpan(2);
    }

    protected function rightColumn(): Group
    {
        return Group::make()->schema([
            Section::make('Status & Organization')->schema([
                Select::make('status')
                    ->options(collect(ProductStatusCast::cases())
                        ->mapWithKeys(fn(ProductStatusCast $status) => [$status->value => $status->getLabel()])
                        ->toArray())
                    ->default(ProductStatusCast::DRAFT->value)
                    ->required()
                    ->native(false),

                Select::make('category_id')
                    ->label('Base Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('url')->required(),
                    ]),

                MultiSelect::make('categories')
                    ->label('Additional Categories')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->helperText('Link the product to other categories to describe it more')
                    ->options(fn (Get $get) => Category::query()
                        ->where('status', true)
                        ->when($get('category_id'), function ($query, $baseId) {
                            $query->where(function ($query) use ($baseId) {
                                $query->where('id', $baseId)
                                    ->orWhere('parent_id', $baseId);
                            });
                        })
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()),
            ]),
            Section::make('Order Qty')
                ->description('Set how many can orders at a time')
                ->collapsible()
                ->collapsed()
                ->columns(1)->schema([
                    TextInput::make('min_quantity')
                        ->label('Min Qty')
                        ->numeric()
                        ->default(1)
                        ->helperText('Minimum quantity sold without stock entry override'),

                    TextInput::make('max_quantity')
                        ->label('Max Qty')
                        ->numeric()
                        ->helperText('Leave empty for no max limit'),

                    TextInput::make('wholesale_unit_quantity')
                        ->label('Wholesale Unit')
                        ->numeric()
                        ->columnSpanFull()
                        ->helperText('Break packs into this quantity when shipped'),
                ]),

            Section::make('Customer Benefit')
                ->description('Common benefits for all')
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextInput::make('reward_points')
                        ->label('Coins (Reward Points)')
                        ->numeric()
                        ->default(0)
                        ->helperText('Customer reward points per purchase'),
                ]),

            Section::make('Affiliate Benefit')
                ->description('Benefits for members and promoters')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('bv')
                            ->label('Business Volume')
                            ->numeric()
                            ->default(0)
                            ->helperText('BV points awarded per unit sold'),

                        TextInput::make('pv')
                            ->label('Personal Volume')
                            ->numeric()
                            ->default(0)
                            ->helperText('PV points for the affiliate'),
                    ]),
                ]),

            Section::make('Distributor Benefit')
                ->collapsible()
                ->collapsed()
                ->description('Wholesale Benefits for distributor')
                ->schema([
                    TextInput::make('commission_rate')
                        ->label('Commission Rate')
                        ->numeric()
                        ->suffix('%')
                        ->helperText('Treats null as using level rate'),

                    Toggle::make('is_commissionable')
                        ->label('Is Commissionable')
                        ->inline(false)
                        ->helperText('Toggle whether this product generates affiliate commissions')
                        ->default(true),
                ]),

            Section::make('Shipping')
                ->collapsed()
                ->collapsible()
                ->schema([
                    TextInput::make('weight_grams'),
                    TextInput::make('length_cm'),
                    TextInput::make('width_cm'),
                    TextInput::make('height_cm'),
                ]),

            Section::make('Settings')->schema([
                Toggle::make('is_returnable')
                    ->label('Returnable Product')
                    ->live()
                    ->default(false),

                TextInput::make('return_days')
                    ->label('Return Window (Days)')
                    ->numeric()
                    ->default(7)
                    ->visible(fn (Get $get) => $get('is_returnable')),

                TextInput::make('view_count')
                    ->numeric()
                    ->readOnly()
                    ->label('Total Views'),
            ]),

            KeyValue::make('seo_meta')
                ->label('SEO Title/Meta')
                ->hint('Add Product Meta Tags ')
                ->addable()
                ->deletable()
                ->addActionLabel('Add Tags'),

        ])->columnSpan(1);
    }

    protected function filterConfigurationFullWidth(): Section
    {
        return Section::make('Filter Configuration')
            ->description('Attach this product to a filter group so the storefront layers the correct filters.')
            ->columnSpanFull()
            ->schema(fn (Get $get, Set $set) => $this->filterConfigurationSection($get, $set));
    }

}
