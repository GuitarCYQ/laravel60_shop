<?php

namespace App\Services\Admin\Product;

use App\Models\Product\ProductBarcodeModel;
use App\Models\System\UserTokenModel;
use Exception;
use Illuminate\Support\Facades\DB;

class ProductBarcodeService
{
    protected $productBarcodeObj; //数据结果集
    public $productBarcodeIds; //查询结果集
    public $productBarcodeId = 0;

    public function __construct($name = '')
    {
        $this->productBarcodeObj = new ProductBarcodeModel();
        if ($name) {
            $this->productBarcodeIds = $this->productBarcodeObj->getByCondition(array('product_sku' => $name));
            if ($this->productBarcodeIds) $this->productBarcodeId = $this->productBarcodeIds[0]['pb_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row 数据
     * @param int $merchantId 用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $productBarcodeId = 0)
    {
        DB::beginTransaction();

        try {

            if (empty($row)) throw new Exception('数据不能为空');

            $UserTokenObj = new UserTokenModel();
            $user = $UserTokenObj->getByCondition(array('key' => $row['key']));
            $row['user_id'] =$user[0]['user_id'];
            unset($row['key']);

            if ($productBarcodeId > 0) {
                if (!$this->productBarcodeObj->find($productBarcodeId)) throw new Exception('产品条码不存在!');

                if (!$this->productBarcodeObj->toUpdate($row, $productBarcodeId)) throw new Exception('产品条码更新失败！');
                $typeStr = '更新';
//
            } else {
                $this->__construct($row['product_sku']);
                if ($this->productBarcodeIds) throw new Exception('产品条码已存在，不需要再创建!');

                if (!$this->productBarcodeObj->toCreate($row)) throw new Exception('产品条码创建失败');
                $typeStr = '创建';

            }

            DB::commit();
            return array('code' => 200, 'message' => '产品条码' . $typeStr . '成功');
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
        $total = $this->productBarcodeObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->productBarcodeIds = $this->productBarcodeObj->getByCondition($condition, '*', '', '', $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->productBarcodeIds);
    }
}
