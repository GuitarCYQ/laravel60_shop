<?php

namespace App\Services\Admin\Shop;

use App\Models\Shop\ShopExpensesModel;
use App\Models\Shop\ShopLogModel;
use Exception;
use Illuminate\Support\Facades\DB;

class ShopExpensesService
{
    protected $shopExpensesObj; //数据结果集
    public $shopExpensesIds; //查询结果集
    public $shopExpensesId = 0;

    public function __construct($name = '')
    {
        $this->shopExpensesObj = new ShopExpensesModel();
        if ($name) {
            $this->shopExpensesIds = $this->shopExpensesObj->getByCondition(array('shop_id' => $name));
            if ($this->shopExpensesIds) $this->shopExpensesId = $this->shopExpensesIds[0]['se_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row 数据
     * @param int $shopId 用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $shopExpensesId = 0)
    {
        DB::beginTransaction();

        try {

            if (empty($row)) throw new Exception('数据不能为空');

            if ($shopExpensesId > 0) {
                if (!$this->shopExpensesObj->find($shopExpensesId)) throw new Exception('店铺费用不存在!');

                if (!$this->shopExpensesObj->toUpdate($row, $shopExpensesId)) throw new Exception('店铺费用更新失败！');
                $typeStr = '更新';
            } else {
                $this->__construct($row['shop_id']);
                if ($this->shopExpensesIds) throw new Exception('店铺费用已存在，不需要再创建!');

                if (!$this->shopExpensesObj->toCreate($row)) throw new Exception('店铺费用创建失败');
                $typeStr = '创建';

            }

            DB::commit();
            return array('code' => 200, 'message' => '店铺收款' . $typeStr . '成功');
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
        $total = $this->shopExpensesObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->shopExpensesIds = $this->shopExpensesObj->getByCondition($condition, '*', '', '', $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->shopExpensesIds);
    }
}
