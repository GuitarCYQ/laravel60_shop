<?php


namespace App\Services\Admin\System;


use App\Models\System\SysConfigModel;
use Exception;
use Illuminate\Support\Facades\DB;

class SysConfigService
{
    protected $sysConfigModelObj;
    public $sysConfigInfo;
    public $sysConfigId = 0;

    public function  __construct($nameArr = array())
    {
        $this->sysConfigModelObj = new SysConfigModel();
        if ($nameArr) {
            $this->sysConfigInfo = $this->sysConfigModelObj->getByCondition($nameArr);
            if ($this->sysConfigInfo) $this->sysConfigId = $this->sysConfigInfo[0]['config_id'];
        }
    }

    /**
     * 创建或更新
     * @param array $row    数据
     * @param int $userId   用户ID
     * @return array
     */
    public function createOrUpdate($row = array(), $actionId = 0)
    {
        //数据表中的字段前缀
        $tableName = 'config_';
        //错误信息的前缀
        $messagePrefix = '系统配置';
        DB::beginTransaction();
        try {
            if (empty($row[$tableName.'property']))  throw new Exception($messagePrefix.'属性不能为空！');
            if (empty($row[$tableName.'value']))  throw new Exception($messagePrefix.'值不能为空！');
            if (empty($row[$tableName.'remark']))  throw new Exception($messagePrefix.'备注不能为空！');

            if ($actionId > 0) {
                $isExistId = $this->sysConfigModelObj->find($actionId); //find()为成员方法
                if (!$isExistId) throw new Exception($messagePrefix.'ID不存在！');
                if (!$this->sysConfigModelObj->toUpdate($row, $actionId)) throw new Exception($messagePrefix.'更新失败！');
                $typeStr = '更新';
            } else {
                //分别判断中英文是否存在
                $nameArr = array($tableName.'property' => $row[$tableName.'property']);
                $this->__construct($nameArr);
                if ($this->sysConfigInfo) throw new Exception($messagePrefix.'已存在，无需创建！');
                if (!$this->sysConfigModelObj->create($row)) throw new Exception($messagePrefix.'创建失败！');
                $typeStr = '创建';
            }
            DB::commit();
            return array('code' => 200, 'message' => $messagePrefix . $typeStr . '成功！');
        } catch (Exception $exc) {
            DB::rollback();
            return array('code' => 400, 'message' => $exc->getMessage());
        }
    }
}
