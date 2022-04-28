<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use Illuminate\Http\Request;
use App\Services\CartService;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        $items = $this->cartService->get();
        $addresses = $request->user()->addresses()->orderBy("last_used_at", "desc")->get();

        return $this->success("获取成功", [
            'items' => $items,
            'addresses' => $addresses
        ]);
    }

    public function add(AddCartRequest $request)
    {
        $skuId  = $request->input('sku_id');
        $amount = $request->input('amount');

        $this->cartService->add($skuId, $amount);

        return $this->success("添加成功",[]);
    }

    public function remove(ProductSku $sku, Request $request)
    {
        $this->cartService->remove($sku->id);
        return $this->success("移除成功",[]);
    }

}
