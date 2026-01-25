<?php

namespace App\Filament\Resources\Ecommerce\Products\Schemas;

use App\Casts\ProductStatusCast;
use App\Casts\ProductTypeCast;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    // LEFT COLUMN (Main Content) - Spans 2 cols
                    Group::make()->schema([
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
                                    ->default('simple')
                                    ->native(false),

                                Select::make('parent_id')
                                    ->relationship('parent', 'name')
                                    ->searchable()
                                    ->placeholder('Select Parent (if variant)'),
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
                    ])->columnSpan(2),

                    // RIGHT COLUMN (Settings & Sidebar) - Spans 1 col
                    Group::make()->schema([
                        Section::make('Status & Organization')->schema([
                            Select::make('status')
                                ->options(collect(ProductStatusCast::cases())
                                    ->mapWithKeys(fn (ProductStatusCast $status) => [$status->value => $status->getLabel()])
                                    ->toArray())
                                ->default(ProductStatusCast::DRAFT->value)
                                ->required()
                                ->native(false),

                            Select::make('category_id')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->createOptionForm([
                                    TextInput::make('name')->required(),
                                    TextInput::make('url')->required(),
                                ]),

                            Select::make('filter_group_id')
                                ->relationship('filterGroup', 'name')
                                ->searchable()
                                ->required()
                                ->helperText('Defines available filters for this product.'),
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

                        Section::make('Pricing')
                            ->schema([
                                Placeholder::make('pricing_info')
                                    ->label('Manage Pricing')
                                    ->content('Pricing and Inventory are managed via the "Stocks" tab after creation. Prices are calculated based on stock landing costs.'),
                            ]),


                        KeyValue::make('seo_meta')
                            ->label('SEO Title/Meta')
                            ->hint('Add Product Meta Tags ')
                            ->addActionLabel('Add Tags'),

                    ])->columnSpan(1),
                ])->columnSpanFull(),
            ]);
    }
}
