<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\Page;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = $this->getFromStorage('private/data/pages.json');


        foreach ($pages as $page) {
//            Page::updateOrCreate(
//                ['slug' => $page->slug, 'prefix' => $page->prefix],
//                $page
//            );


            $newPage = Page::create([
                'slug' => $page->slug,
                'prefix' => $page->prefix,
//                'url' => $page->url,
                'title' => $page->title,
                'content' => $page->content,
                'layout' => $page->layout,
                'template' => $page->template,
                'meta' => $page->meta,
                'sections' => $page->sections,
                'custom_css' => $page->custom_css,
                'custom_js' => $page->custom_js,
                'status' => $page->status,
                'order' => $page->order,
            ]);

        }
    }


    /**
     * @throws Exception
     */
    protected function getFromStorage(string $path)
    {
        // Debug the full path using the base disk instead of 'local'
        $fullPath = storage_path('app/' . $path);
        echo "Looking for file at: {$fullPath}\n";

        if (!file_exists($fullPath)) {
            throw new Exception("File not found: {$path}. Full path: {$fullPath}");
        }

        $content = file_get_contents($fullPath);
        if (!$content) {
            throw new Exception("Empty file: {$path}");
        }

        $decoded = json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in {$path}: " . json_last_error_msg());
        }

        return $decoded;
    }

}
