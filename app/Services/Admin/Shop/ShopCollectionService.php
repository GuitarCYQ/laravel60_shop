<?php

namespace App\Services\Admin\Shop;

use App\Models\Shop\ShopCollectionModel;
use Exception;
use Illuminate\Support\Facades\DB;

class ShopCollectionService
{
    protected $shopCollectionObj; //数据结果集
    public $shopCollectionIds; //查询结果集
    public $shopCollectionId = 0;

    public function __construct($name = '')
    {
        $this->shopCollectionObj = new ShopCollectionModel();
        if ($name) {
            $this->shopCollectionIds = $this->shopCollectionObj->getByCondition(array('name' => $name));
            if ($this->shopCollectionIds) $this->shopCollectionId = $this->shopCollectionIds[0]['sc_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row 数据
     * @param int $merchantId 用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $shopCollectionId = 0)
    {
        DB::beginTransaction();

        try {

            if (empty($row)) throw new Exception('数据不能为空');

            if ($shopCollectionId > 0) {
                if (!$this->shopCollectionObj->find($shopCollectionId)) throw new Exception('店铺收款不存在!');

                if (!$this->shopCollectionObj->toUpdate($row, $shopCollectionId)) throw new Exception('店铺收款更新失败！');
                $typeStr = '更新';
            } else {
                $this->__construct($row['name']);
                if ($this->shopCollectionIds) throw new Exception('店铺收款已存在，不需要再创建!');

                if (!$this->shopCollectionObj->toCreate($row)) throw new Exception('店铺收款创建失败');
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
        $total = $this->shopCollectionObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->shopCollectionIds = $this->shopCollectionObj->getByCondition($condition, '*', '', '', $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->shopCollectionIds);
    }
}
