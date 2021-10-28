<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShopProductRequest;
use App\Services\Admin\Shop\ShopProductService;
use Illuminate\Http\Request;

class ShopProductController extends Controller
{
    //增加或修改
    public function createOrUpdate(ShopProductRequest $request)
    {
        $row = [
            'shop_id'   =>  trim($request->input('shop',0)),
            'code'   =>  trim($request->input('code','')),
            'purchase'   =>  trim($request->input('purchase','0.00')),
            'selling'   =>  trim($request->input('selling','0.00')),
            'status'   =>  trim($request->input('status',1)),
            'imgs'   =>  trim($request->input('imgs','')),
            'describe'   =>  trim($request->input('describe','')),
            'key'   =>  trim($request->header('key',''))
        ];
        $id = trim($request->input('id',0));

        $obj = new ShopProductService();
        return response()->json($obj->createOrUpdate($row,$id));
    }

    //列表
    public function list(Request $request)
    {
        $name = trim($request->input('shop',''));
        $status = trim($request->input('status',''));
        $page = trim($request->input('page',0));
        $pageSize = trim($request->input('pageSize',10));

        $obj = new ShopProductService();
        return response()->json($obj->list($name,$status,$page,$pageSize));
    }
}
