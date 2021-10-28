<?php

namespace App\Services\Admin\Merchant;

use App\Models\Merchant\MerchantModel;
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
}
