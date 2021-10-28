<?php

namespace App\Http\Controllers\Admin\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantRequest;
use App\Services\Admin\Merchant\MerchantService;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function createOrUpdate(MerchantRequest $request)
    {
        $row = array(
            'merchant_name' =>  trim($request->input('chinese_name', '')),
            'merchant_name_en' =>  trim($request->input('english_name', '')),
            'merchant_phone' =>  trim($request->input('phone', '')),
            'merchant_email' =>  trim($request->input('email', '')),
            'merchant_status' =>  trim($request->input('status', '1')),
        );
        $merchantId = $request->input('merchant_id', 0);
        $merchantPassword = $request->input('password');

        $obj = new MerchantService();
        return response()->json($obj->createOrUpdate($row, $merchantId,$merchantPassword));
    }

    public function list(Request $request)
    {
        $merchantName = trim($request->input('name', ''));
        $status = $request->input('status', '');
        $page = $request->input('page', 0);
        $pageSize = $request->input('page_size', 10);

        $obj = new MerchantService();
        return response()->json($obj->list($merchantName, $status, $page, $pageSize));
    }
}
