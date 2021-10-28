<?php

namespace App\Http\Controllers\Admin\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantAddressRequest;
use App\Services\Admin\Merchant\MerchantAddressService;
use Illuminate\Http\Request;

class MerchantAddressController extends Controller
{
    //增加或修改
    public function createOrUpdate(MerchantAddressRequest $request)
    {
        $row = [
            'merchant_id'   =>  trim($request->input('merchant','')),
            'name'  =>  trim($request->input('name','')),
            'merchant_phone'    =>  trim($request->input('phone', '')),
            'country_id'    =>  trim($request->input('country', '')),
            'province'    =>  trim($request->input('province', '')),
            'state_city'    =>  trim($request->input('state_city', '')),
            'county_district'    =>  trim($request->input('county_district', '')),
            'address'    =>  trim($request->input('address', '')),
            'type'    =>  trim($request->input('type', '')),
            'status'    =>  trim($request->input('status', 1)),
        ];
        $id = trim($request->input('id',0));

        $obj = new MerchantAddressService();
        return response()->json($obj->createOrUpdate($row,$id));
    }

    //列表
    public function list(Request $request)
    {

        $merchantName = trim($request->input('name', ''));
        $status = trim($request->input('status', ''));
        $page = trim($request->input('page', 0));
        $pageSize = trim($request->input('page_size', 10));
        $merchant_id = trim($request->input('merchant',''));

        $obj = new MerchantAddressService();
        return response()->json($obj->list($merchantName, $status, $page, $pageSize, $merchant_id));
    }

}
