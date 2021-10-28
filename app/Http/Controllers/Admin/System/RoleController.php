<?php

namespace App\Http\Controllers\Admin\System;

use App\Models\System\RoleModel;
use App\Services\Admin\System\RoleService;
use Illuminate\Http\Request;

class RoleController
{
    // 展示所有角色列表
    public function roleList(Request $request)
    {
        $roleModelObj = new RoleModel();
        $data = $roleModelObj->getByCondition();
        $total = $roleModelObj->getByCondition('', 'count(*)');
        return array('code' => 200, 'message' => '成功', 'total' => $total, 'data' => $data);
    }

    // 创建或更新
    public function createOrUpdate(Request $request)
    {
        $row = array(
            'role_name' => trim($request->input('chname', '')),
            'role_name_en' => trim($request->input('enname', '')),
            'role_status' => (int)trim($request->input('status', 1)),
        );
        $roleId = $request->input('role_id', 0);
        $roleServiceObj = new RoleService();
        $resArr = response()->json($roleServiceObj ->createOrUpdate($row, $roleId));
        return $resArr;
    }

    // 删除角色信息
    public function roleDel(Request $request)
    {

    }

    //管理员修改角色菜单权限
    public function menuAuthority(Request $request)
    {
        $roleId = trim($request->input('roleID'));
        $updateMenuIDs = $request->input('menuIDs');

        $roleServiceObj = new RoleService();
        $res = response()->json($roleServiceObj->menuAuthority($roleId, $updateMenuIDs));
        return $res;
    }


    /**
     * 本地测试权限菜单，（勿删！！）
     */
    /*public function judgeRolePower(Request $request)
    {
        $phone = '17879833632';//需指定数据库已有的手机号
        $username = 'aajge';
        $date = date('YmdHis');
        $str = $phone.' '.$username.' '.$date;
        $restr = encrypt($str);
        $request->headers->set('key', $restr);
        $res = $this->judgeRolePower2($request);
        return $res;
    }*/

    /**
     * 根据当前登录用户的角色给予相关菜单权限
     * @return \Illuminate\Http\JsonResponse
     */
    public function judgeRolePower(Request $request)
    {
        //user登录后获得请求头中的key
        $headerKey = $request->header("key");
        $roleServiceObj = new RoleService();
        $res = response()->json($roleServiceObj->judgeRolePower($headerKey));
        return $res;
    }
}
