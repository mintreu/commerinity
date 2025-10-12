<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Mintreu\LaravelCategory\Models\Category;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // All Fall Under Blog category (AS TOP PARENT OF THOSE CATEGORIES)

        $masterCategory = Category::firstWhere('url','blog');
        if (!$masterCategory)
        {
            $masterCategory = Category::factory()->create(['name' => 'Blog','url' => 'blog']);
        }

        // Ensure an author exists
        $author = User::find(1) ?? User::factory()->create([
            'name' => 'Default Author',
            'email' => 'author@example.com',
        ]);

        /**
         * Category structure
         * Root categories with nested children
         */
        $categoryTree = [
            'ecommerce' => [
                'product-launches',
                'deals-and-discounts',
                'shopping-guides',
                'customer-success-stories',
            ],
            'affiliation' => [
                'affiliate-marketing',
                'network-growth',
                'team-leadership',
                'success-mentorship',
            ],
            'company' => [
                'company-updates',
                'press-releases',
                'career-opportunities',
                'investor-relations',
                'innovation-and-tech',
            ],
            'social-programs' => [
                'community-development',
                'environmental-awareness',
                'education-initiatives',
                'healthcare-programs',
                'charity-events',
                'volunteer-stories',
            ],
        ];

        foreach ($categoryTree as $parentUrl => $childrenUrls) {
            // Create or get the parent category
            $parentCategory = Category::firstOrCreate(
                ['url' => Str::slug($parentUrl)],
                [
                    'name' => Str::title(str_replace('-', ' ', $parentUrl)),
                    'parent_id' => $masterCategory->id
                ]
            );


            foreach ($childrenUrls as $childUrl) {
                $childCategory = Category::firstOrCreate(
                    ['url' => Str::slug($childUrl)],
                    [
                        'name' => Str::title(str_replace('-', ' ', $childUrl)),
                        'parent_id' => $parentCategory->id,
                    ]
                );

                // Create some posts for this child category
                Post::factory(15)->create([
                    'author_id' => $author->id,
                    'author_type' => get_class($author),
                    'category_id' => $childCategory->id,
                ]);
            }
        }
    }
}
