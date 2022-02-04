<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests\OrderRequest;
use App\Models\ProductSku;
use App\Models\UserAddress;
use App\Models\Order;
use Carbon\Carbon;
use App\Jobs\CloseOrder;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Services\OrderService;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query()
            // 使用 with 方法预加载，避免N + 1问题
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate();

        return $this->success([
            'orders' => $orders
        ]);
    }

    public function show(Order $order, Request $request)
    {
        $this->authorize('own', $order);

        return $this->success([
            'order' => $order->load(['items.productSku', 'items.product'])
        ]);
    }

    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user    = $request->user();
        $address = UserAddress::find($request->input('address_id'));

        $result = $orderService->store($user, $address, $request->input('remark'), $request->input('items'));
        return $this->success($result);
    }

}
