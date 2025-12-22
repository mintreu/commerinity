<?php

namespace App\Scopes;

use Illuminate\Http\Request;

class CategoryScope
{

    public function apply($query, Request $request)
    {
        if (! $request->has('category')) {
            return $query;
        }

        $categoryId = $request->get('category');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }

}
