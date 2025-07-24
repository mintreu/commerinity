<?php

namespace Database\Seeders;

use App\Models\Category;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
            ]);

            if ($parentCategory && !empty($item->children))
            {
                foreach ($item->children as $child)
                {
                    $childrenCategory =  $parentCategory->children()->create(Category::factory()->raw([
                        'name' => $child->name,
                        'url' => Str::slug($child->name),
                    ]));

                    if (!empty($child->children))
                    {
                        foreach ($child->children as $subChild)
                        {
                            $subChildrenCategory = $childrenCategory->children()->create(Category::factory()->raw([
                                'name' => $child->name,
                                'url' => Str::slug($child->name),
                            ]));
                        }
                    }
                }
            }


        }


//        Category::factory(10) // Parent Categories
//         ->has( Category::factory()->count(5)
//            //->hasProducts(5)
//            ,'children')
//         ->create();
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
