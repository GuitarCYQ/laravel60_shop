<?php

namespace App\Services\Admin\Shop;

use App\Models\Shop\ShopLogModel;
use App\Models\Shop\ShopModel;
use App\Models\Shop\ShopProductModel;
use App\Models\System\UserTokenModel;
use Exception;
use Illuminate\Support\Facades\DB;

class ShopProductService
{
    protected $shopProductObj; //数据结果集
    public $shopProductIds; //查询结果集
    public $shopProductId = 0;

    public function __construct($name = '')
    {
        $this->shopProductObj = new ShopProductModel();
        if ($name) {
            $this->shopProductIds = $this->shopProductObj->getByCondition(array('shop_id' => $name));
            if ($this->shopProductIds) $this->shopProductId = $this->shopProductIds[0]['sp_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row 数据
     * @param int $shopId 用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $shopProductId = 0)
    {
        DB::beginTransaction();

        try {

            if (empty($row)) throw new Exception('数据不能为空');

            $UserTokenObj = new UserTokenModel();
            $user = $UserTokenObj->getByCondition(array('key' => $row['key']));
            $row['user_id'] =$user[0]['user_id'];
            unset($row['key']);

            if ($shopProductId > 0) {
//                var_dump($row);die();
                if (!$this->shopProductObj->find($shopProductId)) throw new Exception('店铺产品不存在!');

                if (!$this->shopProductObj->toUpdate($row, $shopProductId)) throw new Exception('店铺产品更新失败！');
                $typeStr = '更新';
            } else {
                $this->__construct($row['shop_id']);
                if ($this->shopProductIds) throw new Exception('店铺产品已存在，不需要再创建!');

                if (!$this->shopProductObj->toCreate($row)) throw new Exception('店铺产品创建失败');
                $typeStr = '创建';

            }

            DB::commit();
            return array('code' => 200, 'message' => '店铺产品' . $typeStr . '成功');
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
        $total = $this->shopProductObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->shopProductIds = $this->shopProductObj->getByCondition($condition, '*', '', '', $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->shopProductIds);
    }
}
