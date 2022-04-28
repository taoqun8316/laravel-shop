<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $builder = Product::query()->where("on_sale", true);

        if ($search = $request->input("search","")){
            $like = "%".$search."%";
            $builder->where(function ($query) use ($like) {
                $query->where("title", "like", $like)
                ->orWhere("description", "like", $like)
                ->orWhereHas("skus", function ($query) use ($like){
                    $query->where('title', 'like', $like)->orWhere('description', 'like', $like);
                });
            });
        }

        if ($order = $request->input('order', '')) {
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

        $products = $builder->paginate(16);

        return $this->success("获取成功",[
            'products' => $products,
            'filters'  => [
                'search' => $search,
                'order'  => $order,
            ],
        ]);
    }

    public function show(Product $product, Request $request)
    {
        if (!$product->on_sale) {
            throw new ApiException('商品未上架');
        }

        $favored = false;

        if ($user = $request->user()){
            $favored = boolval($user->favoriteProducts()->find($product->id));
        }

        return $this->success("获取成功", [
            'product' => $product,
            'favored' => $favored
        ]);
    }

    public function favor(Product $product, Request $request)
    {
        $user = $request->user();

        if ($user->favoriteProducts()->find($product->id)){
            return $this->success("收藏成功", []);
        }

        $user->favoriteProducts()->attach($product->id);
        return $this->success("收藏成功", []);
    }

    public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();

        $user->favoriteProducts()->detach($product->id);
        return $this->success("取消收藏成功", []);
    }

    public function favorites(Request $request)
    {
        $products = $request->user()->favoriteProducts()->paginate(16);

        return $this->success("返回成功", [
            'products' => $products
        ]);
    }

}
