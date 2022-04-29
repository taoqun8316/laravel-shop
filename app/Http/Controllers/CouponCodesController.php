<?php

namespace App\Http\Controllers;

use App\Models\CouponCode;
use Carbon\Carbon;

class CouponCodesController extends Controller
{
    public function show($code,Request $request)
    {
        if (!$record = CouponCode::where('code', $code)->first()) {
            abort(404);
        }
        $record->checkAvailable($request->user());

        return $record;
    }
}