<?php


namespace App\Services\Admin\System;

use App\Models\System\UserModel;
use Exception;
use Illuminate\Support\Facades\DB;

class UserService
{
    protected $userModelObj;
    public $userInfo;
    public $userId = 0;

    public function  __construct($phone = '')
    {
        $this->userModelObj = new UserModel();
        if ($phone) {
            $this->userInfo = $this->userModelObj->getByCondition(array('user_phone' => $phone));
            if ($this->userInfo) $this->userId = $this->userInfo[0]['user_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row    数据
     * @param int $userId   用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $userId = 0)
    {
        DB::beginTransaction();
        try {
            if (empty($row))  throw new Exception('数据不能为空！');
            if (empty($row['user_name']) && empty($row['user_name_en'])) throw new Exception('中文名、英文名至少填写一个');
            if (!empty($row['user_name'])) {
                if (!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $row['user_name']))
                    throw new Exception('检查您的中文名且不能少于2位中文字符！');
            }
            if (!empty($row['user_name_en'])) {
                if (!preg_match('/^[A-Za-z]{3}/', $row['user_name_en']))
                    throw new Exception('检查您的英文名且不能少于3位大小写字母！');
            }
            //if (empty($row['user_password'])) throw new Exception('密码不能为空！');
            if (empty($row['user_phone'])) throw new Exception('电话号码不能为空！');
            if (!preg_match('/^0?(13|14|15|17|18)[0-9]{9}$/', $row['user_phone'])) throw new Exception('电话号码错误！');
            //if (!preg_match('/^((0\d{2,3}-\d{7,8})|(1[3584]\d{9}))$/', $row['user_phone'])) throw new Exception('电话号码错误！');
            if (empty($row['user_email'])) throw new Exception('邮箱不能为空！');
            if (!preg_match('/^\w+@[a-zA-Z0-9]{2,10}(?:\.[a-z]{2,4}){1,3}$/', $row['user_email'])) throw new Exception('邮箱错误');

            if (empty($row['user_type']) && $row['user_type'] < 0) throw new Exception('用户类型不能为空！');
            if (empty($row['user_status']) && $row['user_status'] < 0) throw new Exception('用户状态不能为空！');
            if (empty($row['role_id'])) throw new Exception('系统角色不能为空！');

            if ($userId > 0) {
                if (!$this->userModelObj->getByPrimaryKey($userId)) throw new Exception('用户ID不存在！');
                if (!$this->userModelObj->toUpdate($row, $userId)) throw new Exception('用户更新失败！');
                $typeStr = '更新';
            } else {
                if (empty($row['user_password'])) throw new Exception('密码不能为空！');
                $this->__construct($row['user_phone']);
                if ($this->userInfo) throw new Exception('用户已存在，无需创建！');
                $retBool = $this->userModelObj->create($row);
                if (!$retBool) throw new Exception('用户创建失败！');
                $typeStr = '创建';
            }

            DB::commit();
            return array('code' => 200, 'message' => '用户' . $typeStr . '成功！');
        } catch (Exception $exc) {
            DB::rollback();
            return array('code' => 400, 'message' => $exc->getMessage());
        }
    }

    /**
     * 列表
     * @param string $name  用户名
     * @return array
     */
    public function list($name = '', $type = 0, $status = 0, $page = 0, $pageSize = 10) {
        $condition = array(
            'name' => $name,
            'type' => $type,
            'status' => $status,
        );
        $total = $this->userModelObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->userInfo = $this->userModelObj->getByCondition($condition, '*', '', '', $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->userInfo);
    }


}
