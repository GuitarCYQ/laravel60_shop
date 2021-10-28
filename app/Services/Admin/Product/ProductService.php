<?php

namespace App\Services\Admin\Product;

use App\Models\Product\ProductModel;
use App\Models\System\UserTokenModel;
use Exception;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected $productObj; //数据结果集
    public $productIds; //查询结果集
    public $productId = 0;

    public function __construct($name = '')
    {
        $this->productObj = new ProductModel();
        if ($name) {
            $this->productIds = $this->productObj->getByCondition(array('product_sku' => $name));
            if ($this->productIds) $this->productId = $this->productIds[0]['product_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row 数据
     * @param int $merchantId 用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $productId = 0)
    {
        DB::beginTransaction();

        try {

            if (empty($row)) throw new Exception('数据不能为空');

            $UserTokenObj = new UserTokenModel();
            $user = $UserTokenObj->getByCondition(array('key' => $row['key']));
            $row['user_id'] =$user[0]['user_id'];
            unset($row['key']);

            if ($productId > 0) {
                if (!$this->productObj->find($productId)) throw new Exception('产品ID不存在!');

                if (!$this->productObj->toUpdate($row, $productId)) throw new Exception('产品更新失败！');
                $typeStr = '更新';
            } else {
                $this->__construct($row['product_sku']);
                if ($this->productIds) throw new Exception('商品已存在，不需要再创建!');

                if (!$this->productObj->toCreate($row)) throw new Exception('商品创建失败');
                $typeStr = '创建';

            }

            DB::commit();
            return array('code' => 200, 'message' => '商品' . $typeStr . '成功');
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
        $total = $this->productObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->productIds = $this->productObj->getByCondition($condition, '*', '', '', $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->productIds);
    }
}
