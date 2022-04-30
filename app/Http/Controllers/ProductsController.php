<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Models\Product;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use App\Http\Requests\ApplyRefundRequest;
use App\Models\Category;

class ProductsController extends Controller
{
    public function index(Request $request, CategoryService $categoryService)
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

        if ($request->input('category_id') && $category = Category::find($request->input('category_id'))) {
            // 如果这是一个父类目
            if ($category->is_directory) {
                // 则筛选出该父类目下所有子类目的商品
                $builder->whereHas('category', function ($query) use ($category) {
                    // 这里的逻辑参考本章第一节
                    $query->where('path', 'like', $category->path.$category->id.'-%');
                });
            } else {
                // 如果这不是一个父类目，则直接筛选此类目下的商品
                $builder->where('category_id', $category->id);
            }
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
            'categoryTree' => $categoryService->getCategoryTree(),
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

        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku']) // 预先加载关联关系
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at') // 筛选出已评价的
            ->orderBy('reviewed_at', 'desc') // 按评价时间倒序
            ->limit(10) // 取出 10 条
            ->get();

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

    public function applyRefund(Order $order, ApplyRefundRequest $request)
    {
        $this->authorize('own', $order);
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可退款');
        }
        // 判断订单退款状态是否正确
        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已经申请过退款，请勿重复申请');
        }
        // 将用户输入的退款理由放到订单的 extra 字段中
        $extra                  = $order->extra ?: [];
        $extra['refund_reason'] = $request->input('reason');
        // 将订单退款状态改为已申请退款
        $order->update([
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra'         => $extra,
        ]);

        return $order;
    }

}
