<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\System\CarouselModel;
use App\Services\Admin\System\CarouselService;
use Illuminate\Http\Request;

class CarouselController extends Controller
{
    // 轮播列表
    public function carouselList()
    {
        $carouselModelObj = new CarouselModel();
        $data = $carouselModelObj->getByCondition();
        $total = $carouselModelObj->getByCondition('', 'count(*)');
        $arrInfo = array('code' => 200, 'message' => '成功！', 'total' =>$total, 'data' => $data);
        return response()->json($arrInfo);
    }

    // 轮播添加或修改
    public function createOrUpdate(Request $request)
    {
        $row = array(
            'carousel_title' => trim($request->input('title')),
            'carousel_target' => trim($request->input('target')),
            'carousel_imgs' => trim($request->input('imgs')),
            'carousel_sort' => (int)trim($request->input('sort', 0)),
            'carousel_status' => (int)trim($request->input('status', 1)),
        );
        $id = (int)trim($request->input('id'));
        $CarouselServiceObj = new CarouselService();
        $ret = $CarouselServiceObj->createOrUpdate($row, $id);
        return response()->json($ret);
    }
}
