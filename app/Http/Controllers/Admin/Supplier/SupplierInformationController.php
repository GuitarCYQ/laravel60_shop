<?php

namespace App\Http\Controllers\Admin\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierInformationRequest;
use App\Services\Admin\Supplier\SupplierInformationService;
use Illuminate\Http\Request;

class SupplierInformationController extends Controller
{
    //增加或修改
    public function createOrUpdate(SupplierInformationRequest $request)
    {
        $row = [
            'supplier_id'   =>  trim($request->input('supplier',0)),
            'business_license'   =>  trim($request->input('business_license','')),
            'id_imgs'   =>  trim($request->input('id_imgs','')),
            'id_number'   =>  trim($request->input('id_number','')),
            'id_type'   =>  trim($request->input('id_type',0))
        ];
        $id = trim($request->input('id',0));

        $obj = new SupplierInformationService();
        return response()->json($obj->createOrUpdate($row,$id));
    }

    //列表
    public function list(Request $request)
    {
        $supplier = trim($request->input('supplier', ''));
        $status = $request->input('status', '');
        $page = $request->input('page', 0);
        $pageSize = $request->input('page_size', 10);

        $obj = new SupplierInformationService();
        return response()->json($obj->list($supplier, $status, $page, $pageSize));
    }
}
