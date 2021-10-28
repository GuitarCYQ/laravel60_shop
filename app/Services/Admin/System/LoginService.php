<?php
namespace App\Services\Admin\System;

use App\Models\System\UserLoginModel;
use App\Models\System\UserTokenModel;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\System\UserModel;

class LoginService
{

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
            if (!preg_match('/^1((34[0-8]\d{7})|((3[0-3|5-9])|(4[5-7|9])|(5[0-3|5-9])|(66)|(7[2-3|5-8])|(8[0-9])|(9[1|8|9]))\d{8})$/', $row['user_phone'])) throw new Exception('电话号码错误！');
            $this->userObj = new UserLoginModel();
            $usobj = $this->userObj->getByCondition(array('user_phone' => $row['user_phone']));
//            var_dump($usobj);die();

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
            $utObj = $userToken->getByCondition(array('user_id' => $tokenIds['user_id']));


            if ($utObj) {
                $sqltokenIds = [
                    'token' =>  $utObj[0]['token'],
                    'key'   =>  $utObj[0]['key'],
                    'user_name' => $usobj[0]['user_name']
                ];
                return array('code' => 200, 'message' => '该用户已登录', 'data' => $sqltokenIds);
            } else{
                if (!$userToken->create($tokenIds)) throw new Exception('Token写入错误');
            }
            unset($tokenIds['user_id']);
            $tokenIds['user_name'] = $usobj[0]['user_name'];
            DB::commit();
            return array('code' => 200, 'message' => 'token创建成功', 'data' => $tokenIds);

        } catch (Exception $e) {
            DB::rollBack();
            return array('code' => 400, 'message' => $e->getMessage());
        }
    }

    public function logout($row = array())
    {
        DB::beginTransaction();

        try {
            if (empty($row)) throw new Exception('数据不能为空');
            $userToken = new UserTokenModel();
            $utObj = $userToken->getByCondition(array('token' => $row['token'], 'key' => $row['key']));
//            var_dump($utObj);die();
            if (!$utObj) throw new Exception('未登录！');
            if (!$userToken->logout($utObj[0]['user_id'])) throw new Exception('退出失败');
            DB::commit();
            return array('code' => 200, 'message' => '退出成功！');

        } catch (Exception $e) {
            DB::rollBack();
            return array('code' => 400, 'message' => $e->getMessage());
        }
    }



}
