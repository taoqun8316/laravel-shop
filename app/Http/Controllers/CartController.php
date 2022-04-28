<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $items = $request->user()->cartItems()->with(["productSku.product"])->get();

        return $this->success("获取成功", [
            'items' => $items
        ]);
    }

    public function add(AddCartRequest $request)
    {
        $user   = $request->user();
        $skuId  = $request->input('sku_id');
        $amount = $request->input('amount');

        if ($cart = $user->cartItems()->where("product_sku_id", $skuId)->first()){
            $cart->update([
                "amout" => $cart->amount+$amount
            ]);
        }else{
            $cartItem = new CartItem([
                "amount" => $amount
            ]);
            $cartItem->user()->associate($user);
            $cartItem->productSku()->associate($skuId);
            $cartItem->save();
        }

        return $this->success("添加成功",[]);
    }

    public function remove(ProductSku $sku, Request $request)
    {
        $request->user()->cartItems()->where("product_sku_id", $sku->id)->delete();
        return $this->success("移除成功",[]);
    }

}
