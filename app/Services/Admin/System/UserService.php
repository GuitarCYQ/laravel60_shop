<?php


namespace App\Services\Admin\System;

use App\Models\System\Authority\ActionAuthorityModel;
use App\Models\System\SysConfigModel;
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
     * 列表
     * @param string $name  用户名
     * @return array
     */
    public function userList($name = '', $type = 0, $status = 0, $page = 0, $pageSize = 10) {
        $condition = array(
            'name' => $name,
            'type' => $type,
            'status' => $status,
        );
        $total = $this->userModelObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->userInfo = $this->userModelObj->getByCondition($condition, '*', '', '', $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->userInfo);
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
                if (!preg_match("/[\x{4e00}-\x{9fa5}]+/u",  $row['user_name']) && !preg_match("/[_A-Za-z0-9]+/i",  $row['user_name']))
                    throw new Exception('检查您的中文名且不能少于2位字符！');
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
                //当用户更新信息时，不需要更新密码，修改密码为另外的接口
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
     * 管理员重置user密码
     * @param int $userId
     * @return array
     * @throws Exception
     */
    public function resetPassword($userId = 0)
    {
        DB::beginTransaction();
        try {
            if (!$userId) throw new Exception('非法的user_id');
            $isExitID = $this->userModelObj->find($userId);
            if (!$isExitID) throw new Exception('不存在该user_id');

            $sysConfigModelObj = new SysConfigModel();
            //从config表中获取到resetPassword的值 -> 作为重置的密码
            $ret = $sysConfigModelObj->where('config_property', 'resetPassword')->get(['config_value'])->toArray();
            $resetPassword = $ret[0]['config_value'];

            $resetRet = $this->userModelObj->toUpdate(['user_password' => $resetPassword], $userId);
            if (!$resetRet) throw new Exception('密码重置失败，如信息无误请联系管理员！');
            DB::commit();
            return array('code' => 200, 'message' => '密码重置成功');
        } catch (Exception $e) {
            DB::rollBack();
            return array('code' => 400, 'message' => $e->getMessage());
        }
    }

    /**
     * @param string $headerKey    请求头中的key，
     * @param array $row    case 1：忘记密码时传递过来的手机号、密码、确认密码、短信验证码；case 2：修改密码时传递过来的密码、确认密码
     * @return array
     * @throws Exception
     */
    public function modifyOrForgetPassword($headerKey = '', $row = array())
    {
        $param = DB::table('config')->select('config_value')->where('config_property', 'testSMS')->get()->toArray();
        if (!$param)  throw new Exception('数据库异常，请联系管理员！');
        $param = $param[0]->config_value;
        $paramArr = explode(',',$param);
        $uName = $paramArr[0];
        $uPwd = $paramArr[1];
        $uPwd = md5($uPwd.$uName);
        $uTemplate = $paramArr[2];

        DB::beginTransaction();
        try {
            if (empty($headerKey) && empty($row)) throw new Exception('非法请求！！！');
            //请求头中有key值，说明是登录后进行的密码修改
            if (!isset($row['password']) || empty($row['password']))  throw new Exception('新密码不能为空！');
            if (!isset($row['repassword']) || empty($row['repassword']))  throw new Exception('确认密码不能为空！');
            if (!($row['password'] === $row['repassword'])) throw new Exception('前后密码不一致！');
            //case 1:存在请求头 -- 登录后用户修改密码
            if ($headerKey){
                //将请求头中encrypt加密的key解密
                $headerKey = decrypt($headerKey);
                //解密后的字符串前11位为手机号，get it！
                $userPhone = substr($headerKey, 0, 11);
//                $userPhone = $headerKey;
                $ret = $this->userModelObj->toUpdate(['user_password' => md5($row['password'])], $userPhone, 'user_phone');
                if (!$ret) throw new Exception('修改失败，如信息无误请联系管理员');
            } else {
                if (!isset($row['phone']) || empty($row['phone']))  throw new Exception('手机号不能为空！');
                if (!isset($row['verifycode']) || empty($row['verifycode']))  throw new Exception('验证码不能为空！');
                //case 2:不存在请求头 -- 登录页面用户忘记密码
                $phone = $row['phone'];
                $code = rand(1000,9999);
                setcookie('code', $code, time()+600);
                //把URL地址改成你自己就好了，把手机号码和信息模板套进去就行
//                $url='http://api.sms.cn/sms/?ac=send&uid=scarecrow1999&pwd=7adcad1af5e80e9951bf895fbb326976&template=100006&mobile='.$phone.'&content={"code":"'.$code.'"}';
                //获取剩余短信条数
//                $url='http://api.sms.cn/sms/?ac=number&uid=scarecrow1999&pwd=7adcad1af5e80e9951bf895fbb326976&template=100006&mobile='.$phone.'&content={"code":"'.$code.'"}';
                $url="http://api.sms.cn/sms/?ac=number&uid=$uName&pwd=$uPwd&template=$uTemplate&mobile=".$phone.'&content={"code":"'.$code.'"}';
                $data = array();
                $method = 'GET';
                $res = $this->curlPost($url, $data, $method);
                if (!$res) throw new Exception('curl内部错误，请联系管理员');
                $res = json_decode($res); // 将请求URL的string返回值转为json对象
                if ( $res &&  $res->stat == "100") {
                    if ($row['verifycode'] == $code) {
                        $ret = $this->userModelObj->toUpdate(['user_password' => md5($row['password'])], $phone, 'user_phone');
                        if (!$ret) throw new Exception('修改失败，如信息无误请联系管理员');
                    } else throw new Exception('验证码错误，请重新核对');
                } else throw new Exception('短信发送失败！请检查您的手机号；如无误请联系管理员');
            }
            DB::commit();
            return array('code' => 200, 'message' => '密码修改成功！');
        } catch (Exception $e) {
            DB::rollBack();
            return array('code' => 400, 'message' => $e->getMessage());
        }
    }

    /**
     * 管理员修改user的Action权限
     * @param int $userId
     * @param array $updateActionIDs
     * @return array
     * @throws Exception
     */
    public function actionAuthority($userId = 0, $updateActionIDs = array())
    {
        DB::beginTransaction();
        try {
            if (!(isset($userId) && $userId > 0))  throw new Exception('非法的user_id，user_id必须大于0且存在于user_action_authority表！！');

            $ActionAuthorityModelObj = new ActionAuthorityModel();
            //根据role_id从role_menu_authority表获得menu_id结果集
            $allActionIDs = $ActionAuthorityModelObj->getByCondition(['user_id' => $userId]);
            if (!$allActionIDs) throw new Exception('role_menu_authority表中未查询到指定的role_id信息，如信息无误请联系管理员！');

            $arrActionIDs = array();
            //遍历从数据查询获得的menu_id, 放入到一个新的数组
            foreach ($allActionIDs as $value) {
                array_push($arrActionIDs, $value['action_id']);
            }

            $delTotal = 0;
            $addTotal = 0;
            //遍历前端传递过来的role要更新或添加对应的menu_id，如果数据库不存在该menu_id, 则将该记录添加到数据库；如果存在该menu_id，则删除该条记录
            foreach ($updateActionIDs as $item) {
                if (in_array($item, $arrActionIDs)) {
                    $delRes = $ActionAuthorityModelObj->where('user_id', $userId)->where('action_id', $item)->delete();
                    if (!$delRes) throw new Exception('删除user权限时user_action_authority表中未查询到匹配的user_id => action_id信息，如信息无误请联系管理员！');
                    $delTotal += 1;
                } else {
                    $arr = array('user_id' => $userId, 'action_id' => $item);
                    $addRes = $ActionAuthorityModelObj->create($arr);
                    if (!$addRes) throw new Exception('添加user的Action权限时出错，如信息无误请联系管理员！');
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
     * @param string $url 请求的URL
     * @param array $data   携带的参数
     * @param string $method    请求方式
     * @param false $https  是否为HTTPS
     * @return bool|string
     */
    public function curlPost($url = '', $data = array(), $method = '', $https = false)
    {
        $ch = curl_init();   //1.初始化
        curl_setopt($ch, CURLOPT_URL, $url); //2.请求地址
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);//3.请求方式
        //4.参数如下
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//https
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //模拟浏览器
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array('Accept-Encoding: gzip, deflate'));//gzip解压内容
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');

        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }

        if($method == "POST"){//5.post方式的时候添加数据
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);//6.执行
        if (curl_errno($ch)) {//7.如果出错
            return curl_error($ch);
        }
        curl_close($ch);//8.关闭
        return $tmpInfo;

    }
}

