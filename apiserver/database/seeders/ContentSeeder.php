<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ContentType;
use App\Models\Content\ContentCategory;
use App\Models\Content\ContentPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $blogCategories = [
            'Affiliate Growth',
            'Product Insights',
            'Guides',
            'Community',
        ];

        $newsCategories = [
            'Announcements',
            'Partnerships',
            'Events',
            'Industry',
        ];

        $this->seedType(ContentType::Blog, $blogCategories, 14);
        $this->seedType(ContentType::News, $newsCategories, 10);
    }

    private function seedType(ContentType $type, array $categories, int $postCount): void
    {
        $categoryRecords = collect($categories)->map(function (string $name, int $index) use ($type) {
            return ContentCategory::updateOrCreate(
                ['type' => $type->value, 'slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $name . ' updates from the team.',
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        });

        for ($i = 1; $i <= $postCount; $i++) {
            $category = $categoryRecords->random();
            $title = $this->titleFor($type, $i);

            ContentPost::updateOrCreate(
                ['type' => $type->value, 'slug' => Str::slug($title)],
                [
                    'category_id' => $category->id,
                    'title' => $title,
                    'excerpt' => $this->excerptFor($type, $i),
                    'content' => $this->contentFor($type, $i),
                    'cover_image' => null,
                    'author_name' => 'VVIndia Editorial',
                    'seo_title' => $title,
                    'seo_description' => $this->excerptFor($type, $i),
                    'published_at' => now()->subDays($postCount - $i),
                    'is_published' => true,
                ]
            );
        }
    }

    private function titleFor(ContentType $type, int $index): string
    {
        if ($type === ContentType::Blog) {
            return "Growth Blueprint #{$index}: Smart affiliate plays";
        }

        return "Platform Update #{$index}: Key headline";
    }

    private function excerptFor(ContentType $type, int $index): string
    {
        if ($type === ContentType::Blog) {
            return "Actionable ideas and insights to help partners scale faster. Edition {$index}.";
        }

        return "Latest announcement and what it means for members. Bulletin {$index}.";
    }

    private function contentFor(ContentType $type, int $index): string
    {
        if ($type === ContentType::Blog) {
            return "<p>This is a placeholder blog post for edition {$index}. Replace with your real story.</p>";
        }

        return "<p>This is a placeholder news update for bulletin {$index}. Replace with your real announcement.</p>";
    }
}

