<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShopProductStockRequest;
use App\Services\Admin\Shop\ShopProductStockService;
use Illuminate\Http\Request;

class ShopProductStockController extends Controller
{
    //增加或修改
    public function createOrUpdate(ShopProductStockRequest $request)
    {
        $row = [
            'code'   =>  trim($request->input('code','')),
            'stock'   =>  trim($request->input('stock','0')),
            'blocked_stock'   =>  trim($request->input('blocked_stock','0')),
            'key'   =>  trim($request->header('key',''))
        ];
        $id = trim($request->input('id',0));

        $obj = new ShopProductStockService();
        return response()->json($obj->createOrUpdate($row,$id));
    }

    //列表
    public function list(Request $request)
    {
        $name = trim($request->input('shop',''));
        $status = trim($request->input('status',''));
        $page = trim($request->input('page',0));
        $pageSize = trim($request->input('pageSize',10));

        $obj = new ShopProductStockService();
        return response()->json($obj->list($name,$status,$page,$pageSize));
    }
}
