<?php
namespace App\Http\Controllers;

use App\Models\UserAddress;
use Illuminate\Http\Request;
use App\Http\Requests\UserAddressRequest;

class UserAddressesController extends Controller
{
    public function index(Request $request)
    {
        return $this->success("返回成功",[
            'addresses' => $request->user()->addresses,
        ]);
    }

    public function store(UserAddressRequest $request)
    {
        $request->user()->addresses()->create($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        ]));
        return $this->success("添加成功");
    }

    public function edit(UserAddress $userAddress)
    {
        return $this->success("获取成功",[
            'userAddress'=>$userAddress
        ]);
    }

    public function update(UserAddress $userAddress, UserAddressRequest $request)
    {
        $userAddress->update($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone'
        ]));
        return $this->success("修改成功");
    }

}
