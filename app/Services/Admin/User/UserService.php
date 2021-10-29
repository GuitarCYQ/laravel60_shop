<?php


namespace App\Services\Admin\User;
use App\Models\System\UserTokenModel;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\User\UserModel;

class UserService
{
    protected $userObj;
    public $userIds;
    public $userId = 0;
    private $defaultPassword = 'abcd12345';

    public function __construct($name = '') {
        $this->userObj = new UserModel();
        if ($name) {
            $this->userIds = $this->userObj->getByCondition(array('name' => $name));
            if ($this->userIds) $this->userId = $this->userIds[0]['user_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row    数据
     * @param int $userId   用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $userId = 0) {
        DB::beginTransaction();
        try {
            if (empty($row)) throw new Exception('数据不能为空！');
            if (empty($row['user_name'])) throw new Exception('中文名不能为空！');
            if (!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $row['user_name'])) throw new Exception('中文名必须为2位中文字符！');
            if (empty($row['user_name_en'])) throw new Exception('英文名不能为空！');
            if (!preg_match('/^[A-Za-z]{3}/', $row['user_name_en'])) throw new Exception('英文名必须为3位大小写字母！');
            if (empty($row['user_phone'])) throw new Exception('电话号码不能为空！');
            if (!preg_match('/^((0\d{2,3}-\d{7,8})|(1[3584]\d{9}))$/', $row['user_phone'])) throw new Exception('电话号码错误！');
            if (empty($row['user_email'])) throw new Exception('邮箱不能为空！');
            if (!preg_match('/^\w+@[a-zA-Z0-9]{2,10}(?:\.[a-z]{2,4}){1,3}$/', $row['user_email'])) throw new Exception('邮箱错误');

            if ($userId > 0) {
                if (!$this->userObj->find($userId)) throw new Exception('用户ID不存在！');
                if (!$this->userObj->toUpdate($row, $userId)) throw new Exception('用户更新失败！');
                $typeStr = '更新';
            } else {
                $this->__construct($row['user_name']);
                if ($this->userIds) throw new Exception('用户已存在，无需创建！');

                $row['user_password'] = md5($this->defaultPassword);
                if (!$this->userObj->create($row)) throw new Exception('用户创建失败！');
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
        $total = $this->userObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->userIds = $this->userObj->getByCondition($condition, '*', '', '', $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->userIds);
    }

    /**
     * login
     * @param string $row 数据
     * @return array
     */
    public function login($row = array())
    {
        DB::beginTransaction();

        try {
            if (empty($row)) throw new Exception('数据不能为空');
            $usobj = $this->userObj->getByCondition(array('user_phone' => $row['user_phone']));
            if (!$usobj) throw new Exception('用户不存在');
            if ($usobj[0]['user_password'] != $row['user_password']) throw new Exception('密码错误');
            $csrf = csrf_token();
            $date = date('Y-m-d H:i:s');
            $token = encrypt($csrf. ' ' .$date);
            $key = encrypt($usobj[0]['user_phone']. ' ' .$usobj[0]['user_name']. ' ' .$date);
            $tokenIds = [
                'token' =>  $token,
                'key'   =>  $key,
                'user_id'   =>  $usobj[0]['user_id']
            ];
            $userToken = new UserTokenModel();
            if (!$userToken->create($tokenIds)) throw new Exception('Token写入错误');
            DB::commit();
            return array('code' => 200, 'message' => 'token创建成功');


        } catch (Exception $e) {
            DB::rollBack();
            return array('code' => 400, 'message' => $e->getMessage());
        }
    }


}
