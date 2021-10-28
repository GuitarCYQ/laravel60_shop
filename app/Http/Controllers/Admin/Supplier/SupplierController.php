<?php

namespace App\Http\Controllers\Admin\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier\CountryModel;
use App\Models\Supplier\SupplierModel;
use App\Services\Admin\Supplier\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    //创建或更新
    public function createOrUpdate(SupplierRequest $request)
    {
        $row = array(
            'supplier_name' => trim($request->input('name', '')),
            'supplier_phone' => trim($request->input('phone', '')),
            'supplier_email' => trim($request->input('email', '')),
            'country_id' => trim($request->input('country', '')),
            'supplier_province' => trim($request->input('province', '')),
            'supplier_state_city' => trim($request->input('state_city', '')),
            'supplier_county_district' => trim($request->input('county_district', '')),
            'supplier_address' => trim($request->input('address', '')),
            'supplier_status' => trim($request->input('status', 1)),
            'supplier_official_website' => trim($request->input('official_website', '')),
            'key'   =>  trim($request->header('key',''))
        );
        $supplierId = $request->input('supplier_id', 0);
//        var_dump($row);die;

        $obj = new SupplierService();
        return response()->json($obj->createOrUpdate($row, $supplierId));
    }

    //列表
    public function list(Request $request)
    {
        $supplierName = trim($request->input('name', ''));
        $status = $request->input('status', '');
        $page = $request->input('page', 0);
        $pageSize = $request->input('page_size', 10);

        $obj = new SupplierService();
        return response()->json($obj->list($supplierName, $status, $page, $pageSize));
    }

    //地区
    public function placeList($id = 0)
    {
        $obj = new SupplierService();
        return response()->json($obj->placeList($id));
    }


}
