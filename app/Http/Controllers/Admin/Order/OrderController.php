<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Services\Admin\Order\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    //创建或修改
    public function createOrUpdate(Request $request)
    {
        $row = array(
            'merchant_id'    =>  trim($request->input('merchant', 0)),
            'shop_id'    =>  trim($request->input('shop', 0)),
            'product_quantity'    =>  trim($request->input('quantity', 1)),
            'order_status'    =>  trim($request->input('status', 1)),
            'ma_id'    =>  trim($request->input('ma', '')),
            'm_token'   =>  trim($request->header('mtoken','')),
        );
        $id = trim($request->input('id',0));

        $orderObj = new OrderService();
        return response()->json($orderObj->createOrUpdate($row,$id));
    }

    //列表
    public function list(Request $request)
    {
        $orderCode = trim($request->input('code', ''));
        $status = $request->input('status', '');
        $page = $request->input('page', 0);
        $pageSize = $request->input('page_size', 10);

        $obj = new OrderService();
        return response()->json($obj->list($orderCode, $status, $page, $pageSize));
    }
}
