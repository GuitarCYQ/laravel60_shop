<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategoryRequest;
use App\Services\Admin\Product\ProductCategoryService;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function createOrUpdate(ProductCategoryRequest $request)
    {
        $row = [
            'name' =>  trim($request->input('name', '')),
            'imgs' =>  trim($request->input('imgs', '')),
            'parent_id' =>  trim($request->input('parent', '0')),
            'sort' =>  trim($request->input('sort', '0')),
            'status' =>  trim($request->input('status', 1)),
            'key'   =>  trim($request->header('key','')),
        ];
        $productId = $request->get('id', 0);

        $obj = new ProductCategoryService();
        return response()->json($obj->createOrUpdate($row,$productId));
    }

    public function list(Request $request)
    {
        $productName = trim($request->get('name',''));
        $status = trim($request->input('status', ''));
        $page = trim($request->input('page', 0));
        $pageSize = trim($request->input('page_size', 10));
        $sort = trim($request->input('sort', '0'));

        $obj = new ProductCategoryService();
        return response()->json($obj->list($productName,$status,$page,$pageSize,$sort));
    }
}
