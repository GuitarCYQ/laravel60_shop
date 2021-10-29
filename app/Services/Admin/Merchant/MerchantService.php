<?php

namespace App\Services\Admin\Merchant;

use App\Models\Merchant\MerchantModel;
use App\Models\Merchant\MerchantTokenModel;
use Exception;
use Illuminate\Support\Facades\DB;

class MerchantService
{
    protected $merchantObj; //数据结果集
    public $merchantIds; //查询结果集
    public $merchantId = 0;

    public function __construct($name = '')
    {
        $this->merchantObj = new MerchantModel();
        if ($name) {
            $this->merchantIds = $this->merchantObj->getByCondition(array('name' => $name));
            if ($this->merchantIds) $this->merchantId = $this->merchantIds[0]['merchant_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row 数据
     * @param int $merchantId 用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $merchantId = 0, $merchantPassword = '')
    {
        DB::beginTransaction();

        try {

            if (empty($row)) throw new Exception('数据不能为空');
            if (!preg_match('/^((0\d{2,3}-\d{7,8})|(1[3584]\d{9}))$/', $row['merchant_phone'])) throw new Exception('电话号码错误！');

            if ($merchantId > 0) {
                if (!$this->merchantObj->find($merchantId)) throw new Exception('客户ID不存在!');

                if (!$this->merchantObj->toUpdate($row, $merchantId)) throw new Exception('客户更新失败！');
                $typeStr = '更新';
//
            } else {
                if (empty($merchantPassword)) throw new Exception('密码不能为空！');
                $this->__construct($row['merchant_name']);
                if ($this->merchantIds) throw new Exception('客户已存在，不需要再创建!');

                if (!$this->merchantObj->toCreate($row,$merchantPassword)) throw new Exception('客户创建失败');
                $typeStr = '创建';

            }

            DB::commit();
            return array('code' => 200, 'message' => '客户' . $typeStr . '成功');
        } catch (Exception $e) {
            DB::rollBack();
            return array('code' =>  400, 'message' => $e->getMessage());
        }
    }

    /**
     * 列表
     * @param string $name 用户名
     * @return array
     */
    public function list($name = '', $status = 0, $page = 0, $pageSize = 10)
    {
        $condition = array(
            'name'  =>  $name,
            'status'    =>  $status,
        );
        $total = $this->merchantObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->merchantIds = $this->merchantObj->getByCondition($condition, '*', '', '', $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->merchantIds);
    }

    /***
     * 登录
     * @param array $row
     * @throws Exception
     */
    public function login($row = array())
    {
        DB::beginTransaction();
        try {
            if (empty($row)) throw new Exception('数据不能为空');
//            if (!preg_match('/^1((34[0-8]\d{7})|((3[0-3|5-9])|(4[5-7|9])|(5[0-3|5-9])|(66)|(7[2-3|5-8])|(8[0-9])|(9[1|8|9]))\d{8})$/', $row['merchant_phone'])) throw new Exception('电话号码错误！');

            $merchantIds = $this->merchantObj->getByCondition(array('merchant_phone' => $row['merchant_phone']));
            if (!$merchantIds) throw new Exception('用户不存在');

            if ($merchantIds[0]['merchant_password'] !== $row['merchant_password']) throw new Exception('密码错误！');

            $csrf = csrf_token();
            $date = date('Y-m-d H:i:s');
            $token = encrypt($csrf. ' ' .$date);
            $key = encrypt($merchantIds[0]['merchant_phone']. ' ' .$merchantIds[0]['merchant_name']. ' ' .$date);
            $tokenIds = [
                'm_token' =>  $token,
                'm_key'   =>  $key,
                'merchant_id'   =>  $merchantIds[0]['merchant_id']
            ];

            $merchantToken = new MerchantTokenModel();
            $mtObj = $merchantToken->getByCondition(array('merchant_id' => $tokenIds['merchant_id']));
            if ($mtObj) {
                $sqltokenIds = [
                    'm_token' =>  $mtObj[0]['m_token'],
                    'm_key'   =>  $mtObj[0]['m_token'],
                    'merchant_name' => $merchantIds[0]['merchant_name']
                ];
                return array('code' => 200, 'message' => '该用户已登录', 'data' => $sqltokenIds);
            } else{
                if (!$merchantToken->create($tokenIds)) throw new Exception('Token写入错误');
            }

            unset($tokenIds['merchant_id']);
            $tokenIds['merchant_name'] = $merchantIds[0]['merchant_name'];

            DB::commit();
            return array('code' => 200, 'message' => 'token创建成功', 'data' => $tokenIds);

        } catch (Exception $e) {
            DB::rollBack();
            return array('code' => 400, 'message' => $e->getMessage());
        }
    }

    /**
     * 退出
     * @param array $row
     */
    public function logout($row = [])
    {
        DB::beginTransaction();
        try {
            if (empty($row)) throw new Exception('数据不能为空');
            $merchantToken = new MerchantTokenModel();
            $mtObj = $merchantToken->getByCondition(array('m_token' => $row['m_token'], 'm_key' => $row['m_key']));
            if (!$mtObj) throw new Exception('未登录!');
            if (!$merchantToken->logout($mtObj[0]['merchant_id'])) throw new Exception('退出失败');
            DB::commit();
            return array('code' => 200, 'message' => '退出成功！');
        } catch (Exception $e)
        {
            DB::rollBack();
            return array('code' => 400, 'message' => $e->getMessage());
        }
    }

}
