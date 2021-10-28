<?php

namespace App\Services\Admin\Logistics;

use App\Models\Logistics\EcModel;
use App\Models\Logistics\LogisticsModel;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Logistics\LogisticsLogModel;

class LogisticsService
{
    protected $logisticsObj;
    public $logisticsIds;
    public $logisticsId = 0;

    public function __construct($name = '')
    {
        $this->logisticsObj = new LogisticsModel();
        if ($name) {
            $this->logisticsIds = $this->logisticsObj->getByCondition(array('logistics_code' => $name));
            if ($this->logisticsIds) $this->logisticsId = $this->logisticsIds[0]['logistics_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row 数据
     * @param int $userId 用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $logisticsId = 0)
    {
        DB::beginTransaction();
        try {
            if (empty($row)) throw new Exception('数据不能为空！');

            //初始值
            $initial = 1;
            //现值
            $now = 1;
            if ($logisticsId > 0) {
                $ids = $this->logisticsObj->getByCondition(array('logistics_id' => $logisticsId));

                $initial = $ids[0]['logistics_status'];
                $now = $row['logistics_status'];
                $code = $ids[0]['logistics_code'];

                if (!$ids) throw new Exception('该物流不存在');
                if (!$this->logisticsObj->toUpdate($row, $logisticsId)) throw new Exception('物流更新失败！');
                $typeStr = '更新';
            } else {
                $this->__construct($row['logistics_code']);
                if ($this->logisticsIds) throw new Exception('物流已存在，不需要再创建!');

                $getLogisticsId = $this->logisticsObj->createGetLogisticsId($row);
                $code = $this->logisticsObj->getByCondition(array('logistics_id' => $getLogisticsId))[0]['logistics_code'];
                if (!$getLogisticsId) throw new Exception('物流创建失败');

                $typeStr = '创建';
            }

            $newLog = [
                'logistics_code'    =>  $code,
                'original_status'   =>  $initial,
                'now_status'    =>  $now,
                'remarks'   =>  $typeStr,
                'create_time'   =>   date('Y-m-d H:i:s')
            ];
            $logObj = new LogisticsLogModel();
            if (!$logObj->toCreate($newLog)) throw new Exception('物流日志创建失败');

            DB::commit();
            return array('code' => 200, 'message' => '物流' . $typeStr . '成功');
        } catch (Exception $e) {
            DB::rollBack();
            return array('code' =>  400, 'message' => $e->getMessage());
        }
    }

    /**
     * 列表
     * @param string $code = 物理单号
     * @return array
     */
    public function list($code = '', $status = 0, $page = 0, $pageSize = 10)
    {
        $condition = array(
            'logistics_code'  =>  $code,
            'status'    =>  $status,
        );
        $total = $this->logisticsObj->getByCondition($condition, 'count(*)');
        if ($total > 0) $this->logisticsIds = $this->logisticsObj->getByCondition($condition, '*', '', '', $page, $pageSize);

        return array('code' => 200, 'message' => '成功！', 'total' => $total, 'data' => $this->logisticsIds);
    }

    public function getCpmpany()
    {
        $companyObj = new EcModel();
        $total = $companyObj->getCpmpany();
        if ($total > 0) $getCpmpany = $companyObj->getCpmpany();
        return array('code' => 200, 'message' => '成功', 'data' => $getCpmpany);
    }

}






