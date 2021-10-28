<?php

namespace App\Services\Admin\Supplier;

use App\Models\Supplier\SupplierInformationModel;
use App\Models\System\UserTokenModel;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier\SupplierLogModel;
use App\Models\Supplier\CountryModel;

class SupplierInformationService
{
    protected $supplierInfoObj;
    public $supplierInfoIds;
    public $supplierInfoId = 0;

    public function __construct($name = '')
    {
        $this->supplierInfoObj = new SupplierInformationModel();
        if ($name) {
            $this->supplierInfoIds = $this->supplierInfoObj->getByCondition(array('name' => $name));
            if ($this->supplierInfoIds) $this->supplierInfoId = $this->supplierInfoIds[0]['supplier_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row 数据
     * @param int $userId 用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $supplierInfoId = 0)
    {
        DB::beginTransaction();

        try {

            if (empty($row)) throw new Exception('数据不能为空');

            if ($supplierInfoId > 0) {
                if (!$this->supplierInfoObj->find($supplierInfoId)) throw new Exception('供应商信息不存在!');

                if (!$this->supplierInfoObj->toUpdate($row, $supplierInfoId)) throw new Exception('供应商信息更新失败！');
                $typeStr = '更新';
            } else {
                $this->__construct($row['supplier_id']);
                if ($this->supplierInfoIds) throw new Exception('供应商信息已存在，不需要再创建!');

                if (!$this->supplierInfoObj->toCreate($row)) throw new Exception('供应商信息创建失败');
                $typeStr = '创建';

            }

            DB::commit();
            return array('code' => 200, 'message' => '供应商信息' . $typeStr . '成功');
        } catch (Exception $e) {
            DB::rollBack();
            return array('code' =>  400, 'message' => $e->getMessage());
        }
    }

    /**
     * 列表
     * @param string $name = 供应商名称
     * @return array
     */
    public function list($name = '', $status = 0, $page = 0, $pageSize = 10)
    {
        $condition = array(
            'supplier'  =>  $name,
            'status'    =>  $status,
        );
        $total = $this->supplierInfoObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->supplierInfoIds = $this->supplierInfoObj->getByCondition($condition, '*', '', '', $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->supplierInfoIds);
    }
}






