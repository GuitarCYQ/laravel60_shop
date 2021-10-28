<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductBarcodeRequest;
use App\Services\Admin\Product\ProductBarcodeService;
use Illuminate\Http\Request;

class ProductBarcodeController extends Controller
{
    public function createOrUpdate(ProductBarcodeRequest $request)
    {
        $row = [
            'product_sku' =>  trim($request->input('sku', '')),
            'code' =>  trim($request->input('code', '')),
            'type' =>  trim($request->input('type', '1')),
            'weight' =>  trim($request->input('weight', '0.00')),
            'length' =>  trim($request->input('length', '0.00')),
            'width' =>  trim($request->input('width', '0.00')),
            'height' =>  trim($request->input('height', '0.00')),
            'company' =>  trim($request->input('company', '')),
            'specifications' =>  trim($request->input('specifications', '')),
            'purchase' =>  trim($request->input('purchase', '0.00')),
            'key'   =>  trim($request->header('key','')),
        ];
        $productId = $request->get('id', 0);

        $obj = new ProductBarcodeService();
        return response()->json($obj->createOrUpdate($row,$productId));
    }

    public function list(Request $request)
    {
        $productName = trim($request->get('name',''));
        $status = $request->input('status', '');
        $page = $request->input('page', 0);
        $pageSize = $request->input('page_size', 10);

        $obj = new ProductBarcodeService();
        return response()->json($obj->list($productName,$status,$page,$pageSize));
    }
}
