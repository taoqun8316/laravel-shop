<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()->where('on_sale', true)->paginate();

        return $this->success([
            'products' => $products
        ]);
    }
}
