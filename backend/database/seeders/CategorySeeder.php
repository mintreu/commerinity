<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Mintreu\LaravelCategory\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allCategories = $this->getFromStorage('private/data/categories/product-categories.json');

        foreach ($allCategories as $item)
        {
            $parentCategory = Category::factory()->create([
                'name' => $item->name,
                'url' => Str::slug($item->name),
                'status' => true,
            ]);

            $this->attachMediaFromStorage($parentCategory,'displayImage');
            $this->attachMediaFromStorage($parentCategory,'bannerImage');


            if ($parentCategory && !empty($item->children))
            {
                foreach ($item->children as $child)
                {
                    $childrenCategory =  $parentCategory->children()->create(Category::factory()->raw([
                        'name' => $child->name,
                        'url' => Str::slug($child->name),
                    ]));

                    $this->attachMediaFromStorage($childrenCategory,'displayImage');
                    $this->attachMediaFromStorage($childrenCategory,'bannerImage');


                    if (!empty($child->children))
                    {
                        foreach ($child->children as $subChild)
                        {
                            $subChildrenCategory = $childrenCategory->children()->create(Category::factory()->raw([
                                'name' => $subChild->name,
                                'url' => Str::slug($subChild->name),
                            ]));
                            $this->attachMediaFromStorage($subChildrenCategory,'displayImage');
                            $this->attachMediaFromStorage($subChildrenCategory,'bannerImage');

                        }
                    }
                }
            }


        }


    }


    protected function attachMediaFromStorage(Category $category,string $collectionName = 'displayImage'): void
    {
        $img = $collectionName === 'displayImage' ? 'display.jpeg' : 'banner.jpeg';
        $displayImagePath = storage_path('app/private/media/categories/'.$category->url.'/'.$img);
        if (file_exists($displayImagePath))
        {
            $category->addMedia($displayImagePath)->preservingOriginal()->toMediaCollection($collectionName);
        }
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
