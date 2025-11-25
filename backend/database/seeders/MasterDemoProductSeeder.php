<?php

namespace Database\Seeders;

use App\Casts\TaxTypeCast;
use App\Models\TaxCode;
use App\Models\User;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Mintreu\LaravelCategory\Models\Category;
use Mintreu\LaravelProductCatalogue\Casts\ProductTypeCast;
use Mintreu\LaravelProductCatalogue\Models\FilterGroup;
use Mintreu\LaravelProductCatalogue\Models\Product;
use Mintreu\LaravelProductCatalogue\Services\ProductCreationService;
use Mintreu\LaravelProductCatalogue\Services\ProductManager;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class MasterDemoProductSeeder extends Seeder
{
    private array $filterGroups;
    private array $categoryFilterMap = [
        'electronics' => 'Electronics',
        'mobiles-accessories' => 'Electronics',
        'smartphones' => 'Electronics',
        'cases-covers' => 'Electronics',
        'headphones-headsets' => 'Electronics',
        'computers-accessories' => 'Electronics',
        'laptops' => 'Electronics',
        'printers-ink' => 'Electronics',
        'monitors' => 'Electronics',
        'tvs-appliances' => 'Electronics',
        'televisions' => 'Electronics',
        'washing-machines' => 'Electronics',
        'refrigerators' => 'Electronics',
        'fashion' => 'Apparels',
        'mens-fashion' => 'Apparels',
        'womens-fashion' => 'Apparels',
        'kids-fashion' => 'Apparels',
        'home-kitchen' => 'Furniture',
        'furniture' => 'Furniture',
        'cookware-dining' => 'Food Products',
        'home-decor' => 'Furniture',
        'books' => 'Books',
        'sports-outdoors' => 'Apparels',
        'exercise-fitness' => 'Apparels',
        'outdoor-recreation' => 'Apparels',
        'team-sports' => 'Apparels',
        'beauty-health' => 'Ayurveda & Herbal Medicines',
        'skin-care' => 'Ayurveda & Herbal Medicines',
        'hair-care' => 'Ayurveda & Herbal Medicines',
        'personal-care' => 'Ayurveda & Herbal Medicines',
        'toys-games' => 'Toys & Games',
        'action-figures' => 'Toys & Games',
        'puzzles' => 'Toys & Games',
        'building-toys' => 'Toys & Games',
        'automotive' => 'Electronics',
        'car-accessories' => 'Electronics',
        'motorcycle-parts' => 'Electronics',
        'car-electronics' => 'Electronics',
        'baby-care' => 'Apparels',
        'diapering' => 'Apparels',
        'feeding' => 'Food Products',
        'nursery' => 'Furniture',
        'grocery-gourmet-foods' => 'Food Products',
        'snack-foods' => 'Food Products',
        'beverages' => 'Food Products',
        'cooking-baking-supplies' => 'Food Products',
        'spices-and-masalas' => 'Spices & Masala',
        'pet-supplies' => 'Food Products',
        'dog-supplies' => 'Food Products',
        'cat-supplies' => 'Food Products',
        'fish-aquatic-pets' => 'Electronics',
        'office-products' => 'Printables',
        'office-electronics' => 'Electronics',
        'office-furniture' => 'Furniture',
        'office-supplies' => 'Printables',
        'industrial-scientific' => 'Electronics',
        'lab-scientific-products' => 'Electronics',
        'janitorial-sanitation-supplies' => 'Detergents & Cleaners',
        'professional-medical-supplies' => 'Ayurveda & Herbal Medicines',
        'arts-crafts-sewing' => 'Apparels',
        'painting-drawing-art-supplies' => 'Apparels',
        'sewing' => 'Apparels',
        'crafting' => 'Apparels',
        'software' => 'Electronics',
        'business-office' => 'Electronics',
        'antivirus-security' => 'Electronics',
        'operating-systems' => 'Electronics',
        'music' => 'Electronics',
        'cds-vinyl' => 'Electronics',
        'digital-music' => 'Electronics',
        'musical-instruments' => 'Electronics',
        'health' => 'Ayurveda & Herbal Medicines',
        'vitamins-dietary-supplements' => 'Ayurveda & Herbal Medicines',
        'health-care' => 'Ayurveda & Herbal Medicines',
        'medical-equipment' => 'Ayurveda & Herbal Medicines',
        'medicine' => 'Ayurveda & Herbal Medicines',
        'over-the-counter-medication' => 'Ayurveda & Herbal Medicines',
        'prescription-medication' => 'Ayurveda & Herbal Medicines',
        'first-aid' => 'Ayurveda & Herbal Medicines',
        'ayurvedic-medicine' => 'Ayurveda & Herbal Medicines',
        'ayurvedic-supplements' => 'Ayurveda & Herbal Medicines',
        'ashwagandha' => 'Ayurveda & Herbal Medicines',
        'turmeric' => 'Ayurveda & Herbal Medicines',
        'triphala' => 'Ayurveda & Herbal Medicines',
        'herbal-remedies' => 'Ayurveda & Herbal Medicines',
        'neem' => 'Ayurveda & Herbal Medicines',
        'amla' => 'Ayurveda & Herbal Medicines',
        'tulsi' => 'Ayurveda & Herbal Medicines',
        'ayurvedic-personal-care' => 'Ayurveda & Herbal Medicines',
        'ayurvedic-hair-care' => 'Ayurveda & Herbal Medicines',
        'ayurvedic-skin-care' => 'Ayurveda & Herbal Medicines',
        'ayurvedic-oral-care' => 'Ayurveda & Herbal Medicines',
    ];


    protected $categories;
    protected $taxCodes;

    public function __construct()
    {
        $this->filterGroups = $this->getFilterGroups();
        $this->categories = Category::Public()->where('parent_id',null)->with('children')->get();
        $this->taxCodes = TaxCode::where('type',TaxTypeCast::GOODS->value)->get();
    }


    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->categories as $category)
        {
            if (in_array($category->url,[
                'spices-and-masalas',
                'ayurvedic-hair-care',
                'ayurvedic-oral-care',
                'hair-care',
                'home-decor',
                'mens-fashion',
                'womens-fashion',
                'cases-covers'

            ])){
                $filterGroup = FilterGroup::with('filters.options')
                    ->where('name',$this->categoryFilterMap[$category->url])
                    ->first();

                $products = $this->getFromStorage('private/data/products/'.$category->url.'/'.$category->url.'.json');

                $this->seedProducts($products,$category,$filterGroup);
            }
        }


    }


    // METHODS

    protected function seedProducts(array|object $products, Category $category,FilterGroup $filterGroup)
    {
        if ($products)
        {
            foreach ($products as $productInfo)
            {
                // Handle array vs object
                $name = is_array($productInfo) ? $productInfo['name'] : $productInfo->name;
                $url  = is_array($productInfo) ? $productInfo['url']  : $productInfo->url;
                $sku  = is_array($productInfo) ? $productInfo['sku']  : $productInfo->sku;
                $shortDesc = is_array($productInfo) ? $productInfo['short_description']  : $productInfo->short_description;
                $desc = is_array($productInfo) ? $productInfo['description']  : $productInfo->description;

                $hsnTaxCode = $this->taxCodes->random(1)->first();

                $productData = Product::factory()->raw([
                    'name' => $name,
                    'url'   => $url,
                    'sku'   => $sku,
                    'price' => fake()->randomElement([12050,15000,8000,45000]),
                    'type' => ProductTypeCast::CONFIGURABLE,
                    'status' => PublishableStatusCast::PUBLISHED->value,
                    'filter_group_id' => $filterGroup->id,
                    'filter_options' => $this->mapFilterOptions($filterGroup, ProductTypeCast::SIMPLE->value),
                    'tax_code_id' => $hsnTaxCode->id,
                    'short_description' => $shortDesc,
                    'description' => $desc
                ]);


                //$product = ProductCreationService::make($productData)->create();

                $product = ProductManager::create($productData);
                $this->feedTheProduct($product,$category);
            }
        }
    }


    protected function feedTheProduct(Product $product,Category $category)
    {
        // Add Media
        $this->attachMediaFiles($product,$category);

        $product->load('variants');
        $product->variants()->each(function ($variant) use($product,$category){
            $this->attachMediaFilesFromParent($product,$variant,$category);
            //Update Status
            $variant->update(['status' => PublishableStatusCast::PUBLISHED,'max_quantity' => fake()->numberBetween(3,12)]);
        });

        // Add Stocks
        $this->addStock($product);

        // Add Category
        $product->categories()->attach([
            $category->id => [
                'base_category' => $category?->parent_id ?? $category->id,
            ],
        ]);


        // Engagement
        $author = User::firstWhere('email','test@example.com');
        if ($author)
        {
            $newEngagement = $author->productEngagements()->create([
                'product_id' => $product->id,
                'review' => fake()->text,
                'rating' => fake()->randomElement([0,1,2,3,4,5]),
                'helpful_votes' => fake()->randomElement([true,false]),
            ]);

            // WishList
            $newWishList = $author->addToWishlist($product->id);

        }
    }









    // HELPER METHODS

    protected function getMediaFromStorage(string $path): string
    {
        return storage_path('app/private/media/products/'.$path);
    }


    protected function attachMediaFiles(Product $product,Category $category)
    {
        // Add Media
        $displayImagePath = $this->getMediaFromStorage($category->url.'/'.$product->url.'.png');
        if (file_exists($displayImagePath))
        {
            $product->addMedia($displayImagePath)->preservingOriginal()->toMediaCollection('displayImage');
        }
        $bannerImagePath = $this->getMediaFromStorage($category->url.'/'.$product->url.'.png');
        if (file_exists($bannerImagePath))
        {
            $product->addMedia($bannerImagePath)->preservingOriginal()->toMediaCollection('bannerImage');
        }
    }



    protected function attachMediaFilesFromParent(Product $parent, Product $product,Category $category): void
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
                'price' => fake()->randomElement([12050,15000,8000,45000]),
            ]);
        }
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



    /**
     * @return array
     */
    public function getCategoryTree(): array
    {
        $json = Storage::disk('private')->get('data/categories/product-categories.json');
        return json_decode($json, true);
    }

    /**
     * @return array
     */
    public function getFilterGroups(): array
    {
        $json = Storage::disk('private')->get('data/filters/filter-group.json');
        return json_decode($json, true);
    }


    /**
     * @param string $categoryUrl
     * @return array|null
     */
    public function getFilterGroupByCategoryUrl(string $categoryUrl): ?array
    {
        $filterGroupName = $this->categoryFilterMap[$categoryUrl] ?? null;

        if (!$filterGroupName) {
            return null;
        }

        foreach ($this->filterGroups as $filterGroup) {
            if ($filterGroup['name'] === $filterGroupName) {
                return $filterGroup;
            }
        }

        return null;
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


}
