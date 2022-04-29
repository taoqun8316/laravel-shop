<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Exceptions\ApiException;

class PaymentController extends Controller
{
    public function payByAlipay(Order $order, Request $request)
    {
        $this->authorize("own", $order);

        if ($order->paid_at || $order->closed){
            throw new ApiException("订单状态不正确");
        }

        return app("alipay")->web([
            "out_trade_no" => $order->no,
            "total_amount" => $order->total_amount,
            "subject" => '支付 Laravel Shop 的订单：'.$order->no,
        ]);
    }

    public function alipayNotify()
    {
        $data = app('alipay')->verify();

        if(!in_array($data->trade_status, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return app('alipay')->success();
        }

        $order = Order::where('no', $data->out_trade_no)->first();
        if (!$order) {
            return 'fail';
        }

        if ($order->paid_at) {
            return app('alipay')->success();
        }

        $order->update([
            'paid_at'        => Carbon::now(), // 支付时间
            'payment_method' => 'alipay', // 支付方式
            'payment_no'     => $data->trade_no, // 支付宝订单号
        ]);

        return app('alipay')->success();
    }

}
