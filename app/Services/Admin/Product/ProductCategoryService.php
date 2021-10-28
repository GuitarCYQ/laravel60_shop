<?php

namespace App\Services\Admin\Product;

use App\Models\Product\ProductCategoryModel;
use App\Models\System\UserTokenModel;
use Exception;
use Illuminate\Support\Facades\DB;

class ProductCategoryService
{
    protected $productCategoryObj; //数据结果集
    public $productCategoryIds; //查询结果集
    public $productCategoryId = 0;

    public function __construct($name = '')
    {
        $this->productCategoryObj = new ProductCategoryModel();
        if ($name) {
            $this->productCategoryIds = $this->productCategoryObj->getByCondition(array('name' => $name));
            if ($this->productCategoryIds) $this->productCategoryId = $this->productCategoryIds[0]['pc_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row 数据
     * @param int $merchantId 用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $productCategoryId = 0)
    {
        DB::beginTransaction();

        try {

            if (empty($row)) throw new Exception('数据不能为空');

            $UserTokenObj = new UserTokenModel();
            $user = $UserTokenObj->getByCondition(array('key' => $row['key']));
            $row['user_id'] = $user[0]['user_id'];
            unset($row['key']);

            if ($productCategoryId > 0) {
                if (!$this->productCategoryObj->find($productCategoryId)) throw new Exception('产品分类不存在!');

                if (!$this->productCategoryObj->toUpdate($row, $productCategoryId)) throw new Exception('产品分类更新失败！');
                $typeStr = '更新';

            } else {
                $this->__construct($row['name']);
                if ($this->productCategoryIds) throw new Exception('产品分类已存在，不需要再创建!');

                if (!$this->productCategoryObj->toCreate($row)) throw new Exception('产品分类创建失败');
                $typeStr = '创建';

            }

            DB::commit();
            return array('code' => 200, 'message' => '产品分类' . $typeStr . '成功');
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
    public function list($name = '', $status = 0, $page = 0, $pageSize = 10,$sort=0)
    {
        $condition = array(
            'name'  =>  $name,
            'status'    =>  $status,
        );
        $total = $this->productCategoryObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->productCategoryIds = $this->productCategoryObj->getByCondition($condition, '*', '', $sort, $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->productCategoryIds);
    }
}
