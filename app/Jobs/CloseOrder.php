<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CloseOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    public function __construct(Order $order, $delay)
    {
        $this->order = $order;
        $this->delay($delay);
    }

    public function handle()
    {
        if ($this->order->paid_at){
            return;
        }

        \DB::transaction(function (){
            $this->order->update(["closed"=>true]);
            foreach ($this->order->items as $item){
                $item->productSku->addStock($item->amount);
            }
        });
    }
}
