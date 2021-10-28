<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShopCollectionRequest;
use App\Services\Admin\Shop\ShopCollectionService;
use Illuminate\Http\Request;

class ShopCollectionController extends Controller
{
    //添加或修改
    public function createOrUpdate(ShopCollectionRequest $request)
    {
        $row = [
            'name'  =>  trim($request->input('name','')),
            'code'  =>  trim($request->input('code','')),
            'shop_id'  =>  trim($request->input('shop','')),
            'type'  =>  trim($request->input('type',1)),
            'status'  =>  trim($request->input('status',1))
        ];
        $sc_id = trim($request->input('id',0));

        $obj = new ShopCollectionService();
        return response()->json($obj->createOrUpdate($row,$sc_id));
    }

    //列表
    public function list(Request $request)
    {
        $name = trim($request->input('name', ''));
        $status = $request->input('status', '');
        $page = $request->input('page', 0);
        $pageSize = $request->input('page_size', 10);

        $obj = new ShopCollectionService();
        return response()->json($obj->list($name, $status, $page, $pageSize));
    }
}
