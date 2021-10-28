<?php


namespace App\Http\Controllers\Admin\System;

use App\Models\System\SysConfigModel;
use App\Services\Admin\System\SysConfigService;
use Illuminate\Http\Request;

class SysConfigController
{
    // 创建或更新
    public function createOrUpdate(Request $request)
    {
        $prefix = 'config_';
        $row = array(
            $prefix.'property' => trim($request->input('property', '')),
            $prefix.'value' => ($request->input('value', '')),
            $prefix.'remark' => ($request->input('remark', '')),
        );
        $configId = $request->input($prefix.'id', 0);
        $obj = new SysConfigService();
        return response()->json($obj->createOrUpdate($row, $configId));
    }

    // 列表
    public function sysConfigList()
    {
        $sysConfigModelObj = new SysConfigModel();
        $data = $sysConfigModelObj->getByCondition();
        $total = $sysConfigModelObj->getByCondition('','count(*)');
        return array('code' => 200, 'message' => '成功！', 'total' =>$total, 'data' => $data);
    }
}
