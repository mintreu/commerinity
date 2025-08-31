<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageIndexResource;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{


    public function getPages(Request $request)
    {

        $validate = $request->validate([
           'url'    => 'string|nullable'
        ]);


        $result = isset($validate['url'])
            ? Page::where('status', true)
                ->where(function ($q) use ($validate) {
                    $q->where('slug', $validate['url'])
                        ->orWhere('url', 'like', "%{$validate['url']}%");
                })
                ->first()
            : Page::where('status', true)->orderBy('order')->get();


        if ($result)
        {
            return isset($validate['url']) ? PageResource::make($result) : PageIndexResource::collection($result);
        }else{
            return response()->json([
               'success'    => false,
               'message'    => 'page not found!',
               'data'       => [],
            ]);
        }
    }




    public function index()
    {
        $pages = Page::where('status', true)->orderBy('order')->get();
        return PageIndexResource::collection($pages);
    }

    public function show(string $url)
    {

        dd(Page::where('slug',$url)->first());

        $existPage = Page::where('url','%LIKE%',$url)->orWhere('slug','%LIKE%',$url)->first();

        dd($existPage);

        return  PageResource::make($existPage);
    }
}
