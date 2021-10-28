<?php

namespace App\Services\Admin\Merchant;

use App\Models\Merchant\MerchantAddressModel;
use App\Models\Supplier\CountryModel;
use Exception;
use Illuminate\Support\Facades\DB;

class MerchantAddressService
{
    protected $merchantAddressObj; //数据结果集
    public $merchantAddressIds; //查询结果集
    public $merchantAddressId = 0;

    public function __construct($name = '')
    {
        $this->merchantAddressObj = new MerchantAddressModel();
        if ($name) {
            $this->merchantAddressIds = $this->merchantAddressObj->getByCondition(array('name' => $name));
            if ($this->merchantAddressIds) $this->merchantAddressId = $this->merchantAddressIds[0]['ma_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row 数据
     * @param int $merchantAddressId 用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $merchantAddressId = 0)
    {
        DB::beginTransaction();

        try {

            if (empty($row)) throw new Exception('数据不能为空');
//            if (!preg_match('/^((0\d{2,3}-\d{7,8})|(1[3584]\d{9}))$/', $row['merchant_phone'])) throw new Exception('电话号码错误！');

            if ($merchantAddressId   > 0) {
                if (!$this->merchantAddressObj->find($merchantAddressId)) throw new Exception('客户地址不存在!');

                if (!$this->merchantAddressObj->toUpdate($row, $merchantAddressId)) throw new Exception('客户地址更新失败！');
                $typeStr = '更新';
            } else {
                if (!$this->merchantAddressObj->toCreate($row)) throw new Exception('客户地址创建失败');
                $typeStr = '创建';

            }

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
    public function list($name = '', $status = 0, $page = 0, $pageSize = 10, $merchant_id='')
    {
        try {

            if (empty($merchant_id) && $merchant_id < 1) throw new Exception('商户不能为空');

            $condition = array(
                'name'  =>  $name,
                'status'    =>  $status,
                'merchant_id'   => $merchant_id
            );

            $total = $this->merchantAddressObj->getByCondition($condition, 'count(*)');
            $merchantAddressIds = array();
            if ($total > 0) {
                $merchantAddressIds = $this->merchantAddressObj->getByCondition($condition, '*', '', '', $page, $pageSize);
                $countryObj = new CountryModel();
                foreach ($merchantAddressIds as $key => $value)
                {
                    $companyAddress = '';
                    foreach (array($value['country_id'], $value['province'], $value['state_city'], $value['county_district']) as $val){
                        if ($val){
                            $contryId = $countryObj->getplaceList(array('country_id' => $val));
                            if ($contryId) $companyAddress .= $contryId[0]['country_name'] . '-';
                        }
                    }
                    $merchantAddressIds[$key]['company_address'] = $companyAddress ? substr($companyAddress, 0, -1): '';
                }
            }

            return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $merchantAddressIds);
        } catch (Exception $e)
        {
            DB::rollBack();
            return array('code' =>  400, 'message' => $e->getMessage());
        }

    }
}
