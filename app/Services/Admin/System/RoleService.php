<?php

namespace App\Services\Admin\System;

use App\Models\System\Authority\MenuAuthorityModel;
use App\Models\System\MenuModel;
use App\Models\System\RoleModel;
use App\Models\System\UserModel;
use Exception;
use Illuminate\Support\Facades\DB;

class RoleService
{
    protected $roleModelObj;
    public $roleInfo;
    public $roleId = 0;

    public function  __construct($nameArr = array())
    {
        $this->roleModelObj = new RoleModel();
        if ($nameArr) {
            $this->roleInfo = $this->roleModelObj->getByCondition($nameArr);
            if ($this->roleInfo) $this->roleId = $this->roleInfo[0]['role_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row    数据
     * @param int $userId   用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $roleId = 0)
    {
        DB::beginTransaction();
        try {
            if (empty($row))  throw new Exception('数据不能为空！');
            if (empty($row['role_name'])) throw new \Exception('中文名不能为空！');
            if (!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $row['role_name']))
                throw new Exception('检查您的中文名且不能少于2位中文字符！');
            if (empty($row['role_name_en'])) throw new \Exception('英文名不能为空！');
            if (!preg_match('/^[A-Za-z]{3}/', $row['role_name_en']))
                throw new Exception('检查您的英文名且不能少于3位大小写字母！');
            if (empty($row['role_status']) && $row['role_status'] < 0) throw new Exception('角色状态不能为空！');

            if ($roleId > 0) {
                $isExistId = $this->roleModelObj->find($roleId); //find()为成员方法
                if (!$isExistId) throw new Exception('角色ID不存在！');
                if (!$this->roleModelObj->toUpdate($row, $roleId)) throw new Exception('角色更新失败！');
                $typeStr = '更新';
            } else {
                //分别判断中英文是否存在
                $nameArr = array('role_name' => $row['role_name'], 'role_name_en' => $row['role_name_en']);
                $this->__construct($nameArr);
                if ($this->roleInfo) throw new Exception('角色已存在，无需创建！');
                if (!$this->roleModelObj->create($row)) throw new Exception('角色创建失败！');
                $typeStr = '创建';
            }

            DB::commit();
            return array('code' => 200, 'message' => '角色' . $typeStr . '成功！');
        } catch (Exception $exc) {
            DB::rollback();
            return array('code' => 400, 'message' => $exc->getMessage());
        }
    }

    /**
     * 管理员修改role的menu权限
     * @param int $roleId 要修改的role_id
     * @param array $updateMenuIDs 提交时变化的menu_id(多个，存放于array中)
     * @return array
     * @throws Exception
     */
    public function menuAuthority($roleId = 0, $updateMenuIDs = array())
    {
        DB::beginTransaction();
        try {
            if (!(isset($roleId) && $roleId > 0))  throw new Exception('非法的role_id，role_id必须大于0且存在于role_menu_authority表！');

            $MenuAuthorityModelObj = new MenuAuthorityModel();
            //根据role_id从role_menu_authority表获得menu_id结果集
            $allMenuIDs = $MenuAuthorityModelObj->getByCondition(['role_id' => $roleId]);
            if (!$allMenuIDs) throw new Exception('role_menu_authority表中未查询到指定的role_id信息，如信息无误请联系管理员！');

            $arrMenuIDs = array();
            //遍历从数据查询获得的menu_id, 放入到一个新的数组
            foreach ($allMenuIDs as $value) {
                array_push($arrMenuIDs, $value['menu_id']);
            }

            $delTotal = 0;
            $addTotal = 0;
            //遍历前端传递过来的role要更新或添加对应的menu_id，如果数据库不存在该menu_id, 则将该记录添加到数据库；如果存在该menu_id，则删除该条记录
            foreach ($updateMenuIDs as $item) {
                if (in_array($item, $arrMenuIDs)) {
                    $delRes = $MenuAuthorityModelObj->where('menu_id', $item)->where('role_id', $roleId)->delete();
                    if (!$delRes) throw new Exception('删除角色权限时role_menu_authority表中未查询到匹配的role_id => menu_id信息，如信息无误请联系管理员！');
                    $delTotal += 1;
                } else {
                    $arr = array('role_id' => $roleId, 'menu_id' => $item);
                    $addRes = $MenuAuthorityModelObj->create($arr);
                    if (!$addRes) throw new Exception('添加角色权限时出错，如信息无误请联系管理员！');
                    $addTotal += 1;
                }
            }

            DB::commit();
            return array('code' => 200, 'message' => '成功', 'total' =>'删除受影响行数为：'.$delTotal.'，添加受影响行数为：'.$addTotal, 'data' => '');
        } catch (Exception $e) {
            DB::rollBack();
            return array('code' => 400, 'message' => $e->getMessage());
        }
    }

    /**
     * 用户登录后根据其role类型，给予相关菜单权限
     * @param string $headerKey 用户登录后从请求头中获取的 key (备注：encrypt()加密，包含手机号、用户名、date()	格式的时间)
     * @return array
     * @throws Exception
     */
    public function judgeRolePower($headerKey = '')
    {
        DB::beginTransaction();
        try {
            if (!$headerKey)  throw new Exception('未获取到指定的请求头信息');
            //将请求头中encrypt加密的key解密
            $headerKey = decrypt($headerKey);
            //解密后的字符串前11位为手机号，get it！
            $userPhone = substr($headerKey, 0, 11);

            $userModelObj = new UserModel();
            //user表中根据手机号获得role_id
            $matchRole = $userModelObj->getByCondition(['user_phone'=>$userPhone]);
            if (!$matchRole) throw new Exception('user表未查询到指定的信息，如信息无误请联系管理员！');
            //得到对应的role_id整型值（原值为单条记录的二维数组）
            $matchRole = $matchRole[0]['role_id'];

            $roleMenuAuthorityObj = new MenuAuthorityModel();
            //role_menu_authority表中根据role_id获得menu_id结果集
            $roleMatchMenuPower = $roleMenuAuthorityObj->getByCondition(['role_id' => $matchRole]);
            if (!$roleMatchMenuPower) throw new Exception('role_menu_authority表中未查询到指定的信息，如信息无误请联系管理员！');

            $menuModelObj = new MenuModel();
            //menu表中根据menu_id的结果集 -》获得menu表对应的所有字段 -》传递给前端
            $roleMatchMenuList = $menuModelObj->getByCondition(['menu_id' => $roleMatchMenuPower], '*', '', ['menu_sort', 'asc']);

            //第一次循环获得顶级模板array('menu_name' => 'menu_id') ---- 二维数组
            foreach ($roleMatchMenuList as $key => $value) {
                if ($value['menu_parent_id'] == 0) {
                    $arrParentNameId[] = $value;
                }
            }

            //第二次循环将子模板与父模板匹配 ---- 二维数组
            foreach ($arrParentNameId as $k => $v){
                foreach ($roleMatchMenuList as $key => $value) {
                    if ($value['menu_parent_id'] == $v['menu_id']) {
                        $arrParentNameId[$k]['son'][]=$value;
                    }
                }
            }

            $total = $menuModelObj->getByCondition(['menu_id' => $roleMatchMenuPower], 'count(*)');
            if (!$roleMatchMenuList || !$total) throw new Exception('menu表中未查询到指定的信息，如信息无误请联系管理员！');

            DB::commit();
            return array('code' => 200, 'message' => '成功', 'total' => $total, 'data' => $arrParentNameId);
        } catch (Exception $exc) {
            DB::rollBack();
            return array('code' => 400, 'message' => $exc->getMessage());
        }

    }
}
