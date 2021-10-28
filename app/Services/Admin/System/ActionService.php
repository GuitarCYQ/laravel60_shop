<?php


namespace App\Services\Admin\System;


use App\Models\System\ActionModel;
use Exception;
use Illuminate\Support\Facades\DB;

class ActionService
{
    protected $actionModelObj;
    public $actionInfo;
    public $actionId = 0;

    public function  __construct($nameArr = array())
    {
        $this->actionModelObj = new ActionModel();
        if ($nameArr) {
            $this->actionInfo = $this->actionModelObj->getByCondition($nameArr);
            if ($this->actionInfo) $this->actionId = $this->actionInfo[0]['action_id'];
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
        $tableName = 'action_';
        //错误信息的前缀
        $messagePrefix = 'Action';
        DB::beginTransaction();
        try {
            if (empty($row))  throw new Exception('数据不能为空！');
            if (empty($row[$tableName.'name'])) throw new \Exception('中文名不能为空！');
            if (!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $row[$tableName.'name']))
                throw new Exception('检查您的中文名且不能少于2位中文字符！');
            if (empty($row[$tableName.'name_en'])) throw new \Exception('英文名不能为空！');
            if (!preg_match('/^[A-Za-z]{3}/', $row[$tableName.'name_en']))
                throw new Exception('检查您的英文名且不能少于3位大小写字母！');
            if (empty($row[$tableName.'module']))  throw new Exception('所属模块不能为空！');
            if (empty($row[$tableName.'controllers']))  throw new Exception('控制器名称不能为空！');
            if (empty($row[$tableName.'method']))  throw new Exception('方法不能为空！');

            if (empty($row[$tableName.'status']) && $row[$tableName.'status'] < 0) throw new Exception($messagePrefix.'状态不能为空！');

            if ($actionId > 0) {
                $isExistId = $this->actionModelObj->find($actionId); //find()为成员方法
                if (!$isExistId) throw new Exception($messagePrefix.'ID不存在！');
                if (!$this->actionModelObj->toUpdate($row, $actionId)) throw new Exception($messagePrefix.'更新失败！');
                $typeStr = '更新';
            } else {
                //分别判断中英文是否存在
                $nameArr = array($tableName.'name' => $row[$tableName.'name'], $tableName.'name_en' => $row[$tableName.'name_en']);
                $this->__construct($nameArr);
                if ($this->actionInfo) throw new Exception($messagePrefix.'已存在，无需创建！');
                if (!$this->actionModelObj->create($row)) throw new Exception($messagePrefix.'创建失败！');
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
