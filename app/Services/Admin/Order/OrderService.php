<?php

namespace App\Services\Admin\Order;

use App\Models\Merchant\MerchantModel;
use App\Models\Merchant\MerchantTokenModel;
use App\Models\Order\OrderModel;
use App\Models\System\UserModel;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Order\OrderLogModel;

class OrderService
{
    protected $orderObj;
    public $orderIds;
    public $orderId = 0;

    public function __construct($name = '')
    {
        $this->orderObj = new OrderModel();
        if ($name) {
            $this->orderIds = $this->orderObj->getByCondition(array('order_code' => $name));
            if ($this->orderIds) $this->orderId = $this->orderIds[0]['order_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row 数据
     * @param int $userId 用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $orderId = 0)
    {
        DB::beginTransaction();
        try {
            if (empty($row)) throw new Exception('数据不能为空！');

            $merchantObj = new MerchantTokenModel();
            $merchantIds = $merchantObj->getByCondition(array('m_token' => $row['m_token']));
            var_dump($merchantIds);die();
            if (!$merchantIds) throw new Exception('未设置Token');
            $merchantId = $merchantIds[0]['merchant_id'];
            $row['code'] = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            //初始值
            $initial = 1;
            //现值
            $now = 1;
            if ($orderId > 0) {
                $ids = $this->orderObj->getByCondition(array('order_id' => $orderId));

                $initial = $ids[0]['order_status'];
                $now = $row['order_status'];

                if (!$ids) throw new Exception('该订单不存在');
                if (!$this->orderObj->toUpdate($row, $orderId)) throw new Exception('订单更新失败！');
                $typeStr = '更新';
            } else {
                $this->__construct($row['order_code']);
                if ($this->orderIds) throw new Exception('订单已存在，不需要再创建!');

                $getorderId = $this->orderObj->tocreate($row);
                $code = $this->orderObj->getByCondition(array('order_id' => $getorderId))[0]['order_code'];
                if (!$getorderId) throw new Exception('订单创建失败');

                $typeStr = '创建';
            }

            $newLog = [
                'order_code'    =>  $code,
                'original_status'   =>  $initial,
                'now_status'    =>  $now,
                'remarks'   =>  $typeStr,
                'create_time'   =>   date('Y-m-d H:i:s')
            ];
            $logObj = new orderLogModel();
            if (!$logObj->toCreate($newLog)) throw new Exception('订单日志创建失败');

            DB::commit();
            return array('code' => 200, 'message' => '订单' . $typeStr . '成功');
        } catch (Exception $e) {
            DB::rollBack();
            return array('code' =>  400, 'message' => $e->getMessage());
        }
    }

    /**
     * 列表
     * @param string $code = 物理单号
     * @return array
     */
    public function list($code = '', $status = 0, $page = 0, $pageSize = 10)
    {
        $condition = array(
            'order_code'  =>  $code,
            'status'    =>  $status,
        );
        $total = $this->orderObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->orderIds = $this->orderObj->getByCondition($condition, '*', '', '', $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->orderIds);
    }

    public function getCpmpany()
    {
        $companyObj = new EcModel();
        $total = $companyObj->getCpmpany();
        if ($total > 0) $getCpmpany = $companyObj->getCpmpany();
        return array('code' => 200, 'message' => '成功', 'data' => $getCpmpany);
    }

}






