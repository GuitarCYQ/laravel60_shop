<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Services\Admin\Product\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function createOrUpdate(ProductRequest $request)
    {
        $row = [
            'product_sku' =>  trim($request->input('sku', '')),
            'product_name' =>  trim($request->input('chinese_name', '')),
            'product_name_en' =>  trim($request->input('english_name', '')),
            'supplier_id' =>  trim($request->input('supplier', '')),
            'category_id' =>  trim($request->input('category', '')),
            'product_brand' =>  trim($request->input('brand', '')),
            'product_attribute' =>  trim($request->input('attribute', '')),
            'product_imgs' =>  trim($request->input('imgs', '')),
            'product_status' =>  trim($request->input('status', 1)),
            'key'   =>  trim($request->header('key','')),
        ];
        $productId = $request->get('id', 0);

        $obj = new ProductService();
        return response()->json($obj->createOrUpdate($row,$productId));
    }

    public function list(Request $request)
    {
        $productName = trim($request->get('name',''));
        $status = $request->input('status', '');
        $page = $request->input('page', 0);
        $pageSize = $request->input('page_size', 10);

        $obj = new ProductService();
        return response()->json($obj->list($productName,$status,$page,$pageSize));
    }
}
