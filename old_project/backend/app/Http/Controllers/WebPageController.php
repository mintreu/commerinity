<?php

namespace App\Http\Controllers;

use Mintreu\LaravelPenpress\Models\Page;

class WebPageController extends Controller
{
    public function show($url)
    {
        $page = Page::where('url', $url)
            ->orWhere('slug', $url)
            ->firstOrFail();

        // Render specific template if exists, else default
        $template = $page->template ?? 'pages.default';

        return view($template, compact('page'));
    }
}
