<?php

namespace App\Http\Controllers;

use App\Casts\ModelStatusCast;
use App\Casts\ProductTypeCast;
use App\Models\Product;
use Illuminate\Http\Request;

class TestController extends Controller
{
    //


    public function index()
    {

//        $newProduct = Product::create([
//            'name' => 'Chilli Powder',
//            'sku' => 'ccc',
//            'url' => 'chili-powder',
//            'status' =>  ProductStatusCast::DRAFT,
//            'type' => ProductTypeCast::SIMPLE,
//            // 'description' => $this->data['description'] ?? null,
//            'filter_group_id' => 2,
//            //'category_id' => $this->data['category_id'] ?? null,
//
//            // 'parent_id' => $this->data['parent_id'] ?? null,
//        ]);

//
       $chili = Product::with('filterOptions')->firstWhere('url','dfsdfds-786789ykjhk');
//       // $product = Product::with('filters.options')->first();


        dd($chili);


    }







}
