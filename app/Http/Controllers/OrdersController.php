<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Http\Requests\OrderRequest;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\UserAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function store(OrderRequest $request)
    {
        $user = $request->user();
        $order = \DB::transaction(function () use ($user, $request){
            $address = UserAddress::find($request->input("address_id"));
            $address->update([
                "last_used_at" => Carbon::now()
            ]);

            $order = new Order([
                'address' => [ // 将地址信息放入订单中
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark' => $request->input('remark'),
                'total_amount' => 0,
            ]);
            $order->user()->associate($user);
            $order->save();

            $totalAmount = 0;
            $items = $request->input("items");
            foreach ($items as $data){
                $sku  = ProductSku::find($data['sku_id']);
                $item = $order->items()->make([
                    "amount"=>$data['amount'],
                    "price" => $sku->price
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku->id);
                $item->save();
                $totalAmount += $data['amount']*$sku->price;

                //减少库存
                if ($sku->decreaseStock($data['amount'])<=0){
                    throw new ApiException("库存不足");
                }
            }

            $order->update([
                "total_amount" => $totalAmount
            ]);

            $skuIds = collect($items)->pluck("sku_id");
            $user->cartItems()->whereIn("product_sku_id", $skuIds)->delete();

            return $order;
        });

        //(new CloseOrder($order, config('app.order_ttl')))->handle();die;
        $this->dispatch(new CloseOrder($order, config('app.order_ttl')));
        return $this->success("添加成功", [
            "order" => $order
        ]);
    }
}
