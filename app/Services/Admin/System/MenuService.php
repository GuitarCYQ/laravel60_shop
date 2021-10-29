<?php


namespace App\Services\Admin\System;


use App\Models\System\MenuModel;
use Exception;
use Illuminate\Support\Facades\DB;

class MenuService
{
    protected $menuModelObj;
    public $menuInfo;
    public $menuId = 0;

    public function __construct($nameArr = array())
    {
        $this->menuModelObj = new MenuModel();
        if ($nameArr) {
            $this->menuInfo = $this->menuModelObj->getByCondition($nameArr);
            if ($this->menuInfo) $this->menuId = $this->menuInfo[0]['menu_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row    数据
     * @param int $userId   用户ID
     * @return array
     */
    public function createOrUpdate($row = '', $menuId = 0)
    {
        DB::beginTransaction();
        try {
            if (empty($row))  throw new Exception('数据不能为空！');
            if (empty($row['menu_name'])) throw new \Exception('中文名不能为空！');
            if (!preg_match("/[\x{4e00}-\x{9fa5}]+/u", $row['menu_name']) && !preg_match("/[_A-Za-z0-9]+/i", $row['menu_name']))
                throw new Exception('检查您的中文名且不能少于2位字符!');
            if (empty($row['menu_name_en'])) throw new \Exception('英文名不能为空！');
            if (!preg_match('/^[A-Za-z]{3}/', $row['menu_name_en']))
                throw new Exception('检查您的英文名且不能少于3位大小写字母！');
            if (empty($row['menu_css']) ) throw new Exception('css样式不能为空！');
            if (empty($row['menu_sort']) && $row['menu_sort'] < 0) throw new Exception('排序不能为空！');

            //判断添加的是否为顶级模板
            if (!$row['menu_parent_id'] == 0){
                if (!$this->menuModelObj->find($row['menu_parent_id'])) throw new Exception('父模板不存在！');
            }

            if (empty($row['menu_status']) && $row['menu_status'] < 0) throw new Exception('模板状态不能为空！');
            if (empty($row['menu_url']) ) throw new Exception('url不能为空！');

            if ($menuId > 0) {
                $isExistId = $this->menuModelObj->find($menuId); //find()为成员方法
                if (!$isExistId) throw new Exception('菜单ID不存在！');
                if (!$this->menuModelObj->toUpdate($row, $menuId)) throw new Exception('菜单更新失败！');
                $typeStr = '更新';
            } else {
                //分别判断中英文是否存在
                $nameArr = array('menu_name' => $row['menu_name'], 'menu_name_en' => $row['menu_name_en']);
                $this->__construct($nameArr);
                if ($this->menuInfo) throw new Exception('菜单已存在，无需创建！');
                if (!$this->menuModelObj->create($row)) throw new Exception('菜单创建失败！');
                $typeStr = '创建';
            }

            DB::commit();
            return array('code' => 200, 'message' => '菜单' . $typeStr . '成功！');
        } catch (Exception $exc) {
            DB::rollback();
            return array('code' => 400, 'message' => $exc->getMessage());
        }
    }

    /**
     * 菜单列表获取顶级模板
     * @return array
     */
    public function menuShow($roleId = 0)
    {
        if (!$roleId) throw new Exception('非法的roleID!');
        $ret = $this->menuModelObj->select('menu.menu_id','menu.menu_name','menu.menu_parent_id')
            ->rightJoin('role_menu_authority', 'role_menu_authority.menu_id', '=', 'menu.menu_id')
            ->where('menu.menu_status', 1)->where('role_menu_authority.role_id', $roleId)
            ->get()->toArray();
        if (!$ret) throw new Exception('SQL查询出错，请联系管理员!');

        return $ret;
    }
}
