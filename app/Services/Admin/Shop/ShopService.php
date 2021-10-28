<?php

namespace App\Services\Admin\Shop;

use App\Models\Shop\ShopLogModel;
use App\Models\Shop\ShopModel;
use App\Models\System\UserTokenModel;
use Exception;
use Illuminate\Support\Facades\DB;

class ShopService
{
    protected $shopObj; //数据结果集
    public $shopIds; //查询结果集
    public $shopId = 0;

    public function __construct($name = '')
    {
        $this->shopObj = new ShopModel();
        if ($name) {
            $this->shopIds = $this->shopObj->getByCondition(array('name' => $name));
            if ($this->shopIds) $this->shopId = $this->shopIds[0]['shop_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row 数据
     * @param int $shopId 用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $shopId = 0)
    {
        DB::beginTransaction();

        try {

            if (empty($row)) throw new Exception('数据不能为空');
            if ($row['supplier_id'] < 1) throw new Exception('供应商不能为空');
//            if (!preg_match('/^((0\d{2,3}-\d{7,8})|(1[3584]\d{9}))$/', $row['shop_phone'])) throw new Exception('电话号码错误！');

            //获取user_id
            $UserTokenObj = new UserTokenModel();
            $user = $UserTokenObj->getByCondition(array('key' => $row['key']));
            unset($row['key']);

            //初始值
            $initial = 1;
            //现值
            $now = 1;
            if ($shopId > 0) {
                $ids = $this->shopObj->getByCondition(array('shop_id' => $shopId));
                if (!$ids) throw new Exception('店铺不存在');
                if (!$this->shopObj->toUpdate($row, $shopId)) throw new Exception('店铺更新失败！');
                $typeStr = '更新';
                $getShopId = $shopId;

                $initial = $ids[0]['shop_status'];
                $now = 2;
            } else {

                $this->__construct($row['shop_name']);
                if ($this->shopIds) throw new Exception('店铺已存在，不需要再创建!');

                $getShopId = $this->shopObj->createGetShopId($row);
                if (!$getShopId) throw new Exception('店铺创建失败');
                $typeStr = '创建';

            }

            //log日志
            $rowLog = array(
                'shop_id' => $getShopId,
                'original_status' => $initial,
                'now_status'    =>  $now,
                'remarks'   =>  $typeStr,
                'create_time'   => date('Y-m-d H:i:s'),
                'user_id'   =>  $user[0]['user_id']
            );
            $logNew = new ShopLogModel();
            if (!$logNew->create($rowLog)) throw new Exception('店铺日志写入失败');

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
        $total = $this->shopObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->shopIds = $this->shopObj->getByCondition($condition, '*', '', '', $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->shopIds);
    }
}
