<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Ecommerce\Category;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds categories from JSON data file with optional thumbnail images.
     * Data files should be at:
     * - storage/app/private/data/categories/product-categories.json
     * - storage/app/private/data/categories/thumbnail/*.png (optional)
     */
    public function run(): void
    {
        $allCategories = $this->getFromStorage('private/data/categories/product-categories.json');

        foreach ($allCategories as $item) {
            $parentCategory = Category::updateOrCreate(
                ['url' => Str::slug($item->name)],
                [
                    'name' => $item->name,
                    'status' => false,
                ]
            );

            $this->attachThumbnail($parentCategory);
            $this->command->info("Created category: {$parentCategory->name}");

            if ($parentCategory && ! empty($item->children)) {
                foreach ($item->children as $child) {
                    $childCategory = Category::updateOrCreate(
                        ['url' => Str::slug($child->name)],
                        [
                            'name' => $child->name,
                            'parent_id' => $parentCategory->id,
                            'status' => false,
                        ]
                    );

                    $this->attachThumbnail($childCategory);

                    if (! empty($child->children)) {
                        foreach ($child->children as $subChild) {
                            $subChildCategory = Category::updateOrCreate(
                                ['url' => Str::slug($subChild->name)],
                                [
                                    'name' => $subChild->name,
                                    'parent_id' => $childCategory->id,
                                    'status' => false,
                                ]
                            );

                            $this->attachThumbnail($subChildCategory);
                        }
                    }
                }
            }
        }


//        // active specific categories
//
//        Category::whereIn('url', [
//            'spices-masalas',
//            'ayurvedic-hair-care',
//            'ayurvedic-oral-care',
//            'mens-fashion',
//            'cases-covers',
//        ])->update([
//            'status' => true
//        ]);


        self::activateWithParents([
            'spices-masalas',
            'ayurvedic-hair-care',
            'ayurvedic-oral-care',
            'mens-fashion',
            'cases-covers',
        ]);


        $this->command->info('Category seeding completed. Total: '.Category::count());
    }


    public static function activateWithParents(array $urls): void
    {
        $ids = [];

        $categories = Category::with('ancestors') // correct relation
        ->whereIn('url', $urls)
            ->get();

        foreach ($categories as $category) {
            // self + all parents
            $ids[] = $category->id;

            foreach ($category->ancestors as $parent) {
                $ids[] = $parent->id;
            }
        }

        Category::whereIn('id', array_unique($ids))
            ->update(['status' => true]);
    }





    /**
     * Attach thumbnail image using Spatie Media Library
     */
    protected function attachThumbnail(Category $category): void
    {
        $imagePath = storage_path('app/private/data/categories/thumbnail/'.$category->url.'.png');

        if (file_exists($imagePath) && ! $category->hasMedia('thumbnail')) {
            $category->addMedia($imagePath)
                ->preservingOriginal()
                ->toMediaCollection('thumbnail');
        }
    }

    protected function getFromStorage(string $path): mixed
    {
        $fullPath = storage_path('app/'.$path);

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

