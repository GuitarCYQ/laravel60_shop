<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShopRequest;
use App\Services\Admin\Shop\ShopService;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    //添加或修改
    public function createOrUpdate(ShopRequest $request)
    {
        $row = array(
            'shop_name' =>  trim($request->input('chinese_name', '')),
            'shop_name_en' =>  trim($request->input('english_name', '')),
            'supplier_id' =>  trim($request->input('supplier_id', 0)),
            'shop_phone' =>  trim($request->input('phone', '')),
            'shop_email' =>  trim($request->input('email', '')),
            'shop_status' =>  trim($request->input('status', 1)),
            'key'   =>  trim($request->header('key',''))
        );
        $shopId = $request->input('shop_id', 0);
        $obj = new ShopService();
        return response()->json($obj->createOrUpdate($row, $shopId));
    }

    //列表
    public function list(Request $request)
    {
        $shopName = trim($request->input('name', ''));
        $status = $request->input('status', '');
        $page = $request->input('page', 0);
        $pageSize = $request->input('page_size', 10);

        $obj = new ShopService();
        return response()->json($obj->list($shopName, $status, $page, $pageSize));
    }
}
