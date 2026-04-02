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

class ProductCreateForm
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
            $this->typeSelectionSection(),
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

                    Select::make('parent_id')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->placeholder('Select Parent (if bundle)')
                        ->visible(fn (Get $get) => $get('type') === ProductTypeCast::BUNDLE->value),


                ]),
            ]),

        ])
            ->columnSpan(fn (Get $get) => filled($get('type')) ? 2 : 3);
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
        ])
            ->visible(fn (Get $get) => filled($get('type')))
            ->columnSpan(1);
    }

    protected function filterConfigurationFullWidth(): Section
    {
        return Section::make('Filter Configuration')
            ->description('Attach this product to a filter group so the storefront layers the correct filters.')
            ->columnSpanFull()
            ->schema(fn (Get $get, Set $set) => $this->filterConfigurationSection($get, $set))
            ->visible(fn (Get $get) => filled($get('type')));
    }

    protected function typeSelectionSection(): Section
    {
        return Section::make('Choose Product Type First')
            ->description('Select the product type to reveal the rest of the form.')
            ->columnSpanFull()
            ->schema([
                Select::make('type')
                    ->label('Product Type')
                    ->options(fn () => collect(ProductTypeCast::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()]))
                    ->required()
                    ->live()
                    ->native(false)
                    ->columnSpanFull()
                    ->extraInputAttributes(['class' => 'text-lg'])
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                        if ($state !== ProductTypeCast::BUNDLE->value) {
                            $set('parent_id', null);
                        }
                    }),
            ]);
    }

}
