<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserAddressesController extends Controller
{
    public function index(Request $request)
    {
        return $this->success("返回成功",[
            'addresses' => $request->user()->addresses,
        ]);
    }

}
