<?php

namespace App\Services\Admin\Shop;

use App\Models\Shop\ShopLogModel;
use App\Models\Shop\ShopModel;
use App\Models\Shop\ShopProductStockModel;
use App\Models\System\UserTokenModel;
use Exception;
use Illuminate\Support\Facades\DB;

class ShopProductStockService
{
    protected $shopProductStockObj; //数据结果集
    public $shopProductStockIds; //查询结果集
    public $shopProductStockId = 0;

    public function __construct($name = '')
    {
        $this->shopProductStockObj = new ShopProductStockModel();
        if ($name) {
            $this->shopProductStockIds = $this->shopProductStockObj->getByCondition(array('code' => $name));
            if ($this->shopProductStockIds) $this->shopProductStockId = $this->shopProductStockIds[0]['sps_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row 数据
     * @param int $shopId 用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $shopProductStockId = 0)
    {
        DB::beginTransaction();

        try {

            if (empty($row)) throw new Exception('数据不能为空');

            $UserTokenObj = new UserTokenModel();
            $user = $UserTokenObj->getByCondition(array('key' => $row['key']));
            $row['user_id'] =$user[0]['user_id'];
            unset($row['key']);

            if ($shopProductStockId > 0) {
                if (!$this->shopProductStockObj->find($shopProductStockId)) throw new Exception('产品库存不存在!');

                if (!$this->shopProductStockObj->toUpdate($row, $shopProductStockId)) throw new Exception('产品库存更新失败！');
                $typeStr = '更新';
            } else {
                $this->__construct($row['code']);
                if ($this->shopProductStockIds) throw new Exception('产品库存已存在，不需要再创建!');

                if (!$this->shopProductStockObj->toCreate($row)) throw new Exception('产品库存创建失败');
                $typeStr = '创建';

            }

            DB::commit();
            return array('code' => 200, 'message' => '产品库存' . $typeStr . '成功');
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
        $total = $this->shopProductStockObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->shopProductStockIds = $this->shopProductStockObj->getByCondition($condition, '*', '', '', $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->shopProductStockIds);
    }
}
