<?php


namespace App\Http\Controllers\Admin\System;


use App\Http\Controllers\Controller;
use App\Models\System\UserModel;
use App\Models\System\UserTokenModel;
use App\User;
use Illuminate\Http\Request;
use App\Services\Admin\System\UserService;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    // 展示所有用户列表
    public function userList()
    {
        $modelObj = new UserModel();
        $total = $modelObj->getByCondition('', 'count(*)');
        $resArr = $modelObj->getByCondition();
        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $resArr);
    }

    //添加用户
    public function userAdd(Request $request)
    {
        $row = array(
            'user_name' => trim($request->input('chname', '')),
            'user_name_en' => trim($request->input('enname', '')),
            'user_password' => md5(trim($request->input('password', '123456'))),
            'user_phone' => trim($request->input('phone', '')),
            'user_email' => trim($request->input('email', '')),
            'user_type' => (int)$request->input('type', 0), //0：默认系统；1：供应商；2：商户
            'user_status' => (int)$request->input('status', 1), //0：禁用；1：可用
            'role_id' => (int)trim($request->input('role')),
        );
        $userServiceObj = new UserService();
        $resArr =response()->json($userServiceObj->createOrUpdate($row));
        return $resArr;
    }

    //修改用户
    public function userModify(Request $request)
    {
        $row = array(
            'user_name' => trim($request->input('chname', '')),
            'user_name_en' => trim($request->input('enname', '')),
            'user_phone' => trim($request->input('phone', '')),
            'user_email' => trim($request->input('email', '')),
            'user_type' => (int)$request->input('type', 0), //0：默认系统；1：供应商；2：商户
            'user_status' => (int)$request->input('status', 1), //0：禁用；1：可用
            'role_id' => (int)trim($request->input('role')),
        );
        $userId = $request->input('user_id', 0);
        $userServiceObj = new UserService();
        $resArr =response()->json($userServiceObj->createOrUpdate($row, $userId));
        return $resArr;
    }

    // 创建或更新
    public function createOrUpdate(Request $request)
    {
        $row = array(
            'user_name' => trim($request->input('chname', '')),
            'user_name_en' => trim($request->input('enname', '')),
            'user_password' => md5(trim($request->input('password', '123456'))),
            'user_phone' => trim($request->input('phone', '')),
            'user_email' => trim($request->input('email', '')),
            'user_type' => (int)$request->input('type', 0), //0：默认系统；1：供应商；2：商户
            'user_status' => (int)$request->input('status', 1), //0：禁用；1：可用
            'role_id' => (int)trim($request->input('role')),
        );
        $userId = $request->input('user_id', 0);
        $userServiceObj = new UserService();
        $resArr =response()->json($userServiceObj->createOrUpdate($row, $userId));
        return $resArr;
    }

    //管理员修改user的Action权限
    public function actionAuthority(Request $request)
    {
        $userId = trim($request->input('userID'));
        $updateActionIDs = $request->input('actionIDs');

        $userServiceObj = new UserService();
        $res = response()->json($userServiceObj->actionAuthority($userId, $updateActionIDs));
        return $res;
    }

    //管理员重置密码
    public function resetPassword(Request $request)
    {
        $userId = $request->input('user_id');
        $userServiceObj = new UserService();
        $res = $userServiceObj->resetPassword($userId);
        return response()->json($res);
    }

    //用户修改（忘记）密码
    public function modifyOrForgetPassword(Request $request)
    {
        //user登录后获得请求头中的key
        $headerKey = $request->header("key");
        if ($headerKey){
            //case 1:
            //请求头中有key值，说明是登录后进行的 修改密码
            $row = array(
                'password' => trim($request->input('password')),
                'repassword' => trim($request->input('repassword')),
            );
        } else {
            //case 2:
            //请求头中无key值，登录页面点击忘记密码
            $row = array(
                'phone' => trim($request->input('phone')),
                'password' => trim($request->input('password')),
                'repassword' => trim($request->input('repassword')),
                'verifycode' => trim($request->input('verifycode'))
            );
        }
        $userServiceObj = new UserService();
        $res = $userServiceObj->modifyOrForgetPassword($headerKey, $row);
        return response()->json($res);
    }

    // 每隔一段时间删除数据库的token
    public function delDatabaseToken()
    {
        $userTokenModelObj = new UserTokenModel();
        $userTokenModelObj->delToken();
    }

}
