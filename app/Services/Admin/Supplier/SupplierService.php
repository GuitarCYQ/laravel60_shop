<?php

namespace App\Services\Admin\Supplier;

use App\Models\Supplier;
use App\Models\Supplier\SupplierModel;
use App\Models\System\UserTokenModel;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier\SupplierLogModel;
use App\Models\Supplier\CountryModel;

class SupplierService
{
    protected $supplierObj;
    public $supplierIds;
    public $supplierId = 0;

    public function __construct($name = '')
    {
        $this->supplierObj = new SupplierModel();
        if ($name) {
            $this->supplierIds = $this->supplierObj->getByCondition(array('name' => $name));
            if ($this->supplierIds) $this->supplierId = $this->supplierIds[0]['supplier_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row 数据
     * @param int $userId 用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $supplierId = 0)
    {
        DB::beginTransaction();
        try {
            if (empty($row)) throw new Exception('数据不能为空！');
            if (empty($row['supplier_name'])) throw new Exception('名称不能为空！');
            if (empty($row['supplier_phone'])) throw new Exception('电话号码不能为空！');
            if (empty($row['supplier_email'])) throw new Exception('邮箱不能为空！');
            if (!preg_match('/^\w+@[a-zA-Z0-9]{2,10}(?:\.[a-z]{2,4}){1,3}$/', $row['supplier_email'])) throw new Exception('邮箱错误');
            if (empty($row['country_id']) && $row['country_id'] > 0) throw new Exception('国家不能为空');
            if (empty($row['supplier_province']) && $row['supplier_province'] > 0) throw new Exception('省份不能为空');
            if (empty($row['supplier_address'])) throw new Exception('详细地址不能为空');
            if (empty($row['supplier_official_website'])) throw new Exception('官网不能为空');
            if ($row['country_id'] < 1) throw new Exception('国家不能为空');
            if ($row['supplier_province'] < 1) throw new Exception('省份不能为空');
            if ($row['supplier_state_city'] < 1) throw new Exception('州或市不能为空');
//            if ($row['supplier_county_district'] < 1) throw new Exception('县或区不能为空');

            //获取user_id
            $UserTokenObj = new UserTokenModel();
            $user = $UserTokenObj->getByCondition(array('key' => $row['key']));
            unset($row['key']);

            //初始值
            $initial = 1;
            //现值
            $now = 1;
            if ($supplierId > 0) {
                $ids = $this->supplierObj->getByCondition(array('supplier_id' => $supplierId));
                if (!$ids) throw new Exception('供应商不存在');
                if (!$this->supplierObj->toUpdate($row, $supplierId)) throw new Exception('供应商更新失败！');
                $typeStr = '更新';
                $getSupplierId = $supplierId;

                $initial = $ids[0]['supplier_status'];
                $now = 2;
            } else {

                $this->__construct($row['supplier_name']);
                if ($this->supplierIds) throw new Exception('供应商已存在，不需要再创建!');

                $getSupplierId = $this->supplierObj->createGetSupplierId($row);
                if (!$getSupplierId) throw new Exception('供应商创建失败');
                $typeStr = '创建';

            }

            //log日志
            $rowLog = array(
                'supplier_id' => $getSupplierId,
                'original_status' => $initial,
                'now_status'    =>  $now,
                'remarks'   =>  $typeStr,
                'create_time'   => date('Y-m-d H:i:s'),
                'user_id'   =>  $user[0]['user_id']
            );
            $logNew = new SupplierLogModel();
            if (!$logNew->create($rowLog)) throw new Exception('供应商日志写入失败');

            DB::commit();
            return array('code' => 200, 'message' => '供应商' . $typeStr . '成功');
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
            'name' => $name,
            'status' => $status,
        );
        $total = $this->supplierObj->getByCondition($condition, 'count(*)');

        $supplierIds = array();
        if ($total > 0) {
            $supplierIds = $this->supplierObj->getByCondition($condition, '*', '','', $page, $pageSize);
            $countryObj = new CountryModel();

            foreach ($supplierIds as $key => $value) {
                $companyAddress = '';

                foreach (array($value['country_id'], $value['supplier_province'], $value['supplier_state_city'], $value['supplier_county_district']) as $val){
                    if ($val) {
                        $contryId = $countryObj->getplaceList(array('country_id' => $val));
                        if ($contryId) $companyAddress .= $contryId[0]['country_name'] . '-';
                    }
                }
                $supplierIds[$key]['company_address'] = $companyAddress ? substr($companyAddress, 0, -1): '';
            }
        }


        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $supplierIds);
    }

    /**
     * 地方查询
     */
    public function placeList($id = 0)
    {
        $countryObj = new CountryModel();
        $place = $countryObj->getplaceList(array('country_parent_id' => $id));
        return array('code' => 200, 'message' => '成功', 'data' => $place);
    }
}






