<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests\OrderRequest;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\CartService;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()->with([
            'items.product', 'items.productSku'
        ])->where("user_id", $request->user()->id)
            ->orderBy("created_at","desc")->paginate();

        return $this->success("获取成功", [
            "orders" => $orders
        ]);
    }

    public function show(Order $order, Request $request)
    {
        $this->authorize("own", $order);
        return $this->success("获取成功", [
            'order' => $order->load(['items.product', 'items.productSku'])
        ]);
    }

    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user = $request->user();
        $address = UserAddress::find($request->input("address_id"));
        $order = $orderService->store($user, $address, $request->input("remark"),$request->input("items"));

        return $this->success("添加成功", [
            "order" => $order
        ]);
    }
}
