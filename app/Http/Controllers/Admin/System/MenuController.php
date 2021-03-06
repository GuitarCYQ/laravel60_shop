<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\System\Authority\MenuAuthorityModel;
use App\Models\System\MenuModel;
use App\Services\Admin\System\MenuService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    protected $menuModelObj;

    public function __construct()
    {
        $this->menuModelObj = new MenuModel();
    }

    // 创建或更新
    public function createOrUpdate(Request $request)
    {
        $row = array(
            'menu_name' => trim($request->input('chname', '')),
            'menu_name_en' => trim($request->input('enname', '')),
            'menu_css' => $request->input('css', ''),
            'menu_sort' => (int)trim($request->input('sort', 0)),
            'menu_parent_id' => (int)trim($request->input('parent_id', 0)),
            'menu_status' => (int)trim($request->input('status', 1)),
            'menu_url' => trim($request->input('url','')),
        );
        $menuId = $request->input('menu_id', 0);
        $menuServiceObj = new MenuService();
        return response()->json($menuServiceObj->createOrUpdate($row, $menuId));
    }

    // 展示菜单列表
    public function menuList(Request $request)
    {
        $orderBy = ['menu_sort', 'desc'];
        $total = $this->menuModelObj->getByCondition('', 'count(*)');
        $data = $this->menuModelObj->getByCondition('', '*', '', $orderBy);
        return response()->json(array('code' => 200, 'message' => '成功', 'total' => $total, 'data' => $data));
    }

    //菜单列表获取顶级模板
    public function getParentIDs()
    {
        $total = $this->menuModelObj->getByCondition(['menu_parent_id' => 0, 'menu_status' => 1], 'count(*)');
        $data = $this->menuModelObj->getByCondition(['menu_parent_id' => 0, 'menu_status' => 1]);
        return response()->json(array('code' => 200, 'message' => '成功', 'total' => $total, 'data' => $data));
    }

    // 管理员给role分配权限时传递给前端展示的数据
    public function menuShow(Request $request)
    {
        $roleId = (int)trim($request->input('roleId'));
        $menuServiceObj = new MenuService();
        $ret = $menuServiceObj->menuShow($roleId);
        return response()->json(array('code' => 200, 'message' => '成功', 'data' => $ret));
    }


}
