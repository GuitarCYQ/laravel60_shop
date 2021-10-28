<?php

namespace App\Services\Admin\System;

use App\Models\System\RoleModel;
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
}
