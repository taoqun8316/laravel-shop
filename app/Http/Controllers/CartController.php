<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;

class CartController extends Controller
{
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


}
