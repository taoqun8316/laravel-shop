<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Exceptions\ApiException;
use Endroid\QrCode\QrCode;

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

        $this->afterPaid($order);

        return app('alipay')->success();
    }


    public function payByWechat(Order $order, Request $request) {
        // 校验权限
        $this->authorize('own', $order);
        // 校验订单状态
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }
        // scan 方法为拉起微信扫码支付
        $wechatOrder = app('wechat_pay')->scan([
            'out_trade_no' => $order->no,  // 商户订单流水号，与支付宝 out_trade_no 一样
            'total_fee' => $order->total_amount * 100, // 与支付宝不同，微信支付的金额单位是分。
            'body'      => '支付 Laravel Shop 的订单：'.$order->no, // 订单描述
        ]);

        $qrCode = new QrCode($wechatOrder->code_url);
        return $this->success("获取成功", [
            "code_string" => $qrCode->writeString(),
            "Content-Type" => $qrCode->getContentType()
        ]);
    }

    public function wechatNotify()
    {
        $data  = app('wechat_pay')->verify();
        // 找到对应的订单
        $order = Order::where('no', $data->out_trade_no)->first();
        // 订单不存在则告知微信支付
        if (!$order) {
            return 'fail';
        }
        // 订单已支付
        if ($order->paid_at) {
            return app('wechat_pay')->success();
        }

        $order->update([
            'paid_at'        => Carbon::now(),
            'payment_method' => 'wechat',
            'payment_no'     => $data->transaction_id,
        ]);

        $this->afterPaid($order);

        return app('wechat_pay')->success();
    }

    protected function afterPaid($order)
    {
        event(new OrderPaid($order));
    }
}
