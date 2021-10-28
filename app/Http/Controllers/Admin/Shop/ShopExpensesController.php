<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShopExpensesRequest;
use App\Services\Admin\Shop\ShopExpensesService;
use Illuminate\Http\Request;

class ShopExpensesController extends Controller
{
    //添加或修改
    public function createOrUpdate(ShopExpensesRequest $request)
    {
        $row = [
            'shop_id'   =>  trim($request->input('shop','')),
            'price'   =>  trim($request->input('price','0.00')),
            'favorable_price'   =>  trim($request->input('favorable_price','0.00')),
            'actual_price'   =>  trim($request->input('actual_price','0.00')),
            'type'   =>  trim($request->input('type','1')),
            'status'   =>  trim($request->input('status','1')),
            'expiration_time'   =>  trim($request->input('expiration_time',''))
        ];

        $se_id = trim($request->input('id','0'));

        $obj = new ShopExpensesService();
        return response()->json($obj->createOrUpdate($row,$se_id));
    }

    //列表
    public function list(Request $request)
    {
        $name = trim($request->input('shop',''));
        $status = trim($request->input('status',''));
        $page = trim($request->input('page',0));
        $pageSize = trim($request->input('pageSize',10));

        $obj = new ShopExpensesService();
        return response()->json($obj->list($name,$status,$page,$pageSize));
    }
}
