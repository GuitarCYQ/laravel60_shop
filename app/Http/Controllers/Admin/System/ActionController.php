<?php


namespace App\Http\Controllers\Admin\System;


use App\Models\System\ActionModel;
use App\Services\Admin\System\ActionService;
use Illuminate\Http\Request;

class ActionController
{
    // 列表
    public function actionList()
    {
        $actionModelObj = new ActionModel();
        $data = $actionModelObj->getByCondition();
        $total = $actionModelObj->getByCondition('','count(*)');
        return array('code' => 200, 'message' => '成功！', 'total' =>$total, 'data' => $data);
    }

    // 创建或更新
    public function createOrUpdate(Request $request)
    {
        $row = array(
            'action_name' => trim($request->input('chname', '')),
            'action_name_en' => trim($request->input('enname', '')),
            'action_module' => trim($request->input('module', '')),
            'action_controllers' => trim($request->input('controller', '')),
            'action_method' => trim($request->input('method', '')),
            'action_status' => (int)trim($request->input('status', 1)),
        );
        $actionId = $request->input('action_id', 0);
        $obj = new ActionService();
        return response()->json($obj->createOrUpdate($row, $actionId));
    }

    // 管理员分配user权限时传递给前端展示的数据
    public function actionShow()
    {
        $actionServiceObj = new ActionService();
        $ret = $actionServiceObj->actionShow();
        return $ret;
    }
}
