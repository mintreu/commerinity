<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Mintreu\LaravelCategory\Models\Category;
use Mintreu\Toolkit\Casts\PublishableStatusCast;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Load curated posts JSON
        $all = $this->getFromStorage('private/data/posts/prebuild_posts.json'); // {"posts":[...]}
        $posts = $all['posts'] ?? [];

        // Ensure top-level Blog category
        $masterCategory = Category::firstWhere('url', 'blog')
            ?? Category::factory()->create(['name' => 'Blog', 'url' => 'blog']); // safe if category package uses factories [web:2][web:3]

        // Resolve pre-existing default author (do NOT create)
        $defaultAuthorEmail = config('blog.default_author_email', 'test@example.com');
        $defaultAuthor = User::where('email', $defaultAuthorEmail)->first();
        if (! $defaultAuthor) {
            throw new Exception("Default author not found by email: {$defaultAuthorEmail}. Create this user before running PostSeeder.");
        }

        foreach ($posts as $i => $item) {
            // Required fields from JSON
            $parentUrl = Str::slug((string)($item['parent'] ?? ''));
            $childUrl  = Str::slug((string)($item['category'] ?? ''));
            $title     = trim((string)($item['name'] ?? ''));
            $html      = (string)($item['description_html'] ?? '');

            if ($parentUrl === '' || $childUrl === '' || $title === '' || $html === '') {
                throw new Exception("Invalid post at index {$i}: parent, category, name, and description_html are required.");
            }

            // Ensure parent/child categories exist under Blog
            $parentCategory = Category::firstOrCreate(
                ['url' => $parentUrl],
                ['name' => Str::title(str_replace('-', ' ', $parentUrl)), 'parent_id' => $masterCategory->id]
            ); // idempotent category creation [web:3]

            $childCategory = Category::firstOrCreate(
                ['url' => $childUrl],
                ['name' => Str::title(str_replace('-', ' ', $childUrl)), 'parent_id' => $parentCategory->id]
            ); // idempotent category creation [web:3]

            // Slug + status
            $slug   = trim((string)($item['url'] ?? ''));
            $slug   = $slug !== '' ? Str::slug($slug) : $childUrl.'-'.Str::slug($title);
            $status = strtolower((string)($item['status'] ?? 'published'));
            $enum   = PublishableStatusCast::tryFrom($status) ?? PublishableStatusCast::PUBLISHED; // enum-backed cast [web:26]

            // Author resolution: only use existing users; otherwise fallback to default
            $author = $defaultAuthor;
            $authorEmail = trim((string)Arr::get($item, 'author.email', ''));
            if ($authorEmail !== '') {
                $existing = User::where('email', $authorEmail)->first(); // no create [web:3]
                if ($existing) {
                    $author = $existing;
                }
            }

            // Merge with factory defaults (without creating users)
            $factory = Post::factory()->make([
                'name'        => $title,
                'url'         => $slug,
                'description' => $html,
                'category_id' => $childCategory->id,
                'author_id'   => $author->id,
                'author_type' => get_class($author),
                'status'      => $enum,
            ]); // factory helps fill optional attributes consistently [web:2]

            $payload = array_merge(
                Arr::only($factory->getAttributes(), [
                    'name', 'url', 'description', 'category_id', 'author_id', 'author_type', 'status', 'status_feedback',
                ]),
                Arr::only($item, ['status_feedback']) // allow JSON override for status_feedback if provided
            );

            // Idempotent upsert by unique route key 'url'
            $post = Post::updateOrCreate(['url' => $slug], $payload); // recommended for seeding reference content [web:2][web:89]

            // Optional media attach from storage/app relative paths
            $banner    = Arr::get($item, 'media.banner');
            $thumbnail = Arr::get($item, 'media.thumbnail');

            if ($thumbnail) {
                $thumbPath = $this->storageAppPath($thumbnail);
                if (is_file($thumbPath)) {
                    $post->addMedia($thumbPath)->preservingOriginal()->toMediaCollection('displayImage'); // integrates with defined collection [web:3]
                }
            }

            if ($banner) {
                $bannerPath = $this->storageAppPath($banner);
                if (is_file($bannerPath)) {
                    $post->addMedia($bannerPath)->preservingOriginal()->toMediaCollection('bannerImage'); // integrates with defined collection [web:3]
                }
            }
        }
    }

    protected function storageAppPath(string $relative): string
    {
        return rtrim(storage_path('app'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.ltrim($relative, DIRECTORY_SEPARATOR); // standard storage path resolution [web:2]
    }

    protected function getFromStorage(string $path): array
    {
        $fullPath = $this->storageAppPath($path);
        if (! is_file($fullPath)) {
            throw new Exception("File not found: {$path} (full: {$fullPath})");
        }

        $content = file_get_contents($fullPath);
        if ($content === false || $content === '') {
            throw new Exception("Empty file: {$path}");
        }

        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in {$path}: ".json_last_error_msg());
        }

        return $decoded;
    }
}
