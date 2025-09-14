<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Mintreu\LaravelCategory\Models\Category;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Models\FilterGroup;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\LaravelProductCatalogue\Services\ProductCreationService;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class ProductDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoMasalaProducts = $this->getFromStorage('private/data/products/demo-products.json');
        $masalaCategory = Category::firstWhere('url','spices-masalas');
        $masalaFilterGroup = FilterGroup::with('filters.options')->where('name','Spices & Masala')->first();


        foreach ($demoMasalaProducts as $productInfo)
        {


            $productData = Product::factory()->raw([
                'name' => $productInfo->name,
                'url'   => $productInfo->url,
                'sku'   => $productInfo->sku,
                'price' => $productInfo->price,
                'type' => ProductTypeCast::CONFIGURABLE,
                'status' => PublishableStatusCast::PUBLISHED->value,
                'filter_group_id' => $masalaFilterGroup->id,
                'filter_options' => $this->mapFilterOptions($masalaFilterGroup, ProductTypeCast::SIMPLE->value),
            ]);


            $product = ProductCreationService::make($productData)->create();


            // Add Media
           $this->attachMediaFiles($product);

           $product->load('variants');
            $product->variants()->each(function ($variant) use($product){
                $this->attachMediaFilesFromParent($product,$variant);
                //Update Status
                $variant->update(['status' => PublishableStatusCast::PUBLISHED,'max_quantity' => fake()->numberBetween(3,12)]);
            });

            // Add Stocks
            $this->addStock($product);

            // Add Category
            $product->categories()->attach([
                $masalaCategory->id => [
                    'base_category' => $masalaCategory?->parent_id,
                ],
            ]);




        }



    }


    protected function attachMediaFiles(Product $product)
    {
        // Add Media
        $displayImagePath = $this->getMediaFromStorage($product->url.'.jpg');
        if (file_exists($displayImagePath))
        {
            $product->addMedia($displayImagePath)->preservingOriginal()->toMediaCollection('displayImage');
        }
        $bannerImagePath = $this->getMediaFromStorage($product->url.'.jpg');
        if (file_exists($bannerImagePath))
        {
            $product->addMedia($bannerImagePath)->preservingOriginal()->toMediaCollection('bannerImage');
        }
    }


//    protected function attachMediaFilesFromParent(Product $parent,Product $product)
//    {
//        // Add Media
//        $displayImagePath = $parent->getFirstMediaUrl('displayImage');
//        if (!is_null($displayImagePath))
//        {
//            $product->addMediaFromUrl($displayImagePath)->preservingOriginal()->toMediaCollection('displayImage');
//        }
//        $bannerImagePath = $parent->getFirstMediaUrl('bannerImage');
//        if (!is_null($bannerImagePath))
//        {
//            $product->addMediaFromUrl($bannerImagePath)->preservingOriginal()->toMediaCollection('bannerImage');
//        }
//    }



    protected function attachMediaFilesFromParent(Product $parent, Product $product): void
    {
        // Helper function to add media from URL or fallback to path
        $addMediaSafely = function ($mediaCollection) use ($parent, $product) {
            $url = $parent->getFirstMediaUrl($mediaCollection);
            $path = $parent->getFirstMediaPath($mediaCollection);

            if ($url) {
                try {
                    $product->addMediaFromUrl($url)
                        ->preservingOriginal()
                        ->toMediaCollection($mediaCollection);
                    return;
                } catch (\Exception $e) {
                    // URL failed, fallback to path
                    // optional: log the error
                    info("Failed to fetch media from URL: {$url}, falling back to path. Error: " . $e->getMessage());
                }
            }

            // Fallback to local path
            if ($path && file_exists($path)) {
                $product->addMedia($path)
                    ->preservingOriginal()
                    ->toMediaCollection($mediaCollection);
            }
        };

        $addMediaSafely('displayImage');
        $addMediaSafely('bannerImage');
    }






    private function addStock(Product $product): void
    {
        $stockRanges = [
            [200, 300],
            [50, 150],
            [100, 200],
        ];

        foreach ($stockRanges as $range) {
            $product->tiers()->create([
                'init_quantity' => fake()->numberBetween($range[0], $range[1]),
                'sold_quantity' => 0,
                'min_quantity' => 1,
                'max_quantity' => 10,
                'price' => fake()->randomElement([50, 100, 150, 200]),
            ]);
        }
    }



    protected function getMediaFromStorage(string $path): string
    {
        return storage_path('app/private/media/products/'.$path);
    }

    protected function getFromStorage(string $path)
    {
        // Debug the full path using the base disk instead of 'local'
        $fullPath = storage_path('app/'.$path);
        echo "Looking for file at: {$fullPath}\n";

        if (! file_exists($fullPath)) {
            throw new Exception("File not found: {$path}. Full path: {$fullPath}");
        }

        $content = file_get_contents($fullPath);
        if (! $content) {
            throw new Exception("Empty file: {$path}");
        }

        $decoded = json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in {$path}: ".json_last_error_msg());
        }

        return $decoded;
    }


    private function mapFilterOptions($filterGroup, string $productType): array
    {
        $isConfigurable = $productType === 'configurable';

        return $filterGroup->filters->mapWithKeys(function ($filter) use ($isConfigurable) {
            $options = $filter->options;

            if ($options->isEmpty()) {
                return [(string) $filter->id => []];
            }

            if ($isConfigurable) {
                $selected = $options->random(min(2, $options->count()))->pluck('id')->values()->toArray();
            } else {
                $selected = [$options->random()->id];
            }

            // Always cast to string to match form data format
            $selected = array_map('strval', $selected);

            return [(string) $filter->id => $selected];
        })->toArray();
    }



}
