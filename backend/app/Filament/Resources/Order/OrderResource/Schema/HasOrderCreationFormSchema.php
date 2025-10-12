<?php

namespace App\Filament\Resources\Order\OrderResource\Schema;

use Filament\Forms\Components;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;
use Mintreu\LaravelMoney\LaravelMoney;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

trait HasOrderCreationFormSchema
{



    public function chooseProducts()
    {

        // Cache the product query for 5 minutes
        $allProducts = Cache::remember('published_products_with_media', 300, function () {
            return Product::with('media')
                ->where('status', PublishableStatusCast::PUBLISHED->value)
                ->get();
        });


        return Components\Repeater::make('products')
            ->columnSpanFull()
            ->columns(3)
            ->schema([

                Components\Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        // Product select
                        Components\Select::make('product_id')
                            ->label('Select Product')
                            ->options(fn() => $allProducts?->pluck('name', 'id') ?? [])
                            ->required()
                            ->live()
                            ->columnSpan(1),

                        // Quantity select
                        Components\Select::make('quantity')
                            ->label('Quantity')
                            ->visible(fn(Get $get) => $get('product_id'))
                            ->inlineLabel()
                            ->live()
                            ->options(function (Get $get) use($allProducts) {
                                $productId = $get('product_id');

                                if (! $productId) return [];

                                $product = $allProducts->firstWhere('id', $productId);

                                $min = $product->min_quantity ?? 1;
                                $max = $product->max_quantity ?? 10;

                                return collect(range($min, $max))->mapWithKeys(fn($n) => [$n => $n])->toArray();
                            })
                            ->required()
                            ->default(1)
                            ->columnSpan(1),

                        // Overview placeholder (e.g., price × quantity)
                        Components\Placeholder::make('overview')
                            ->visible(fn(Get $get) => $get('quantity'))
                            ->inlineLabel()
                            ->content(function (Get $get) use($allProducts){
                                $productId = $get('product_id');
                                $quantity = $get('quantity') ?? 1;

                                if (! $productId) return null;

                                $product = $allProducts->firstWhere('id', $productId);
                                if (! $product) return null;

                                $price = $product->price ?? 0;
                                $total = $price * $quantity;

                                return new HtmlString("<div class='text-sm '><strong>Total:</strong> " . LaravelMoney::format($total) . "</div>");
                            })
                            ->columnSpan(1),
                    ]),

                // Product info card
                Components\Placeholder::make('product_info')
                    ->label('Product Info')
                    ->content(function (\Filament\Forms\Get $get) use($allProducts) {
                        $productId = $get('product_id');

                        if (! $productId) return 'Select a product to see details.';

                        $product = $allProducts->firstWhere('id', $productId);
                        if (! $product) return 'Product not found.';

                        $imageUrl = $product->getFirstMediaUrl('displayImage') ?: 'https://placehold.co/120x120';
                        $returnable = $product->is_returnable ? 'Yes' : 'No';
                        $price = LaravelMoney::format($product->price ?? 0);
                        $type = $product->type->getLabel() ?? 'N/A';
                        $minQty = $product->min_quantity ?? 1;
                        $maxQty = $product->max_quantity ?? '∞';

                        return new HtmlString("
                    <div class='flex flex-col lg:flex-row gap-4 items-start p-4 rounded-md shadow-sm'>
                        <!-- Image -->
                        <img class='object-cover w-20 h-20 flex-shrink-0 rounded-md border' src='{$imageUrl}' alt='{$product->name}' />

                        <!-- Details -->
                        <div class='flex-1 flex flex-col gap-3'>
                            <!-- Top: Name, SKU, Price -->
                            <div class='flex flex-col lg:flex-row lg:gap-6 w-full text-sm'>
                                <div class='w-full lg:w-1/2 space-y-1'>
                                    <div class='text-lg'><strong>Name:</strong> {$product->name}</div>
                                    <div class='text-gray-500'><strong>SKU:</strong> {$product->sku}</div>
                                    <div><strong>Price:</strong> {$price}</div>
                                </div>
                                <!-- Secondary details -->
                                <div class='w-full lg:w-1/2 space-y-1 mt-2 lg:mt-0 text-gray-500'>
                                    <div><strong>Type:</strong> {$type}</div>
                                    <div><strong>Stock Limits:</strong> Min {$minQty} - Max {$maxQty}</div>
                                    <div><strong>Returnable:</strong> {$returnable}</div>
                                </div>
                            </div>

                            <!-- Short description -->
                            <div class='text-sm text-gray-700'>
                                <strong>Short Description:</strong> {$product->short_description}
                            </div>
                        </div>
                    </div>
                ");
                    })
                    ->columnSpan(2),
            ]);




    }


}
