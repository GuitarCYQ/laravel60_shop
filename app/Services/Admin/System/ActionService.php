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
            if (!preg_match("/[\x{4e00}-\x{9fa5}]+/u", $row[$tableName.'name']) && !preg_match("/[_A-Za-z0-9]+/i", $row[$tableName.'name']))
                throw new Exception('检查您的中文名且不能少于2位字符！');
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

    /**
     *  管理员分配user权限时传递给前端展示的数据
     * @return array
     */
    public function actionShow()
    {
        DB::connection()->enableQueryLog();
        try {
            // 获取到actiond的modules
            $modules = $this->actionModelObj->actionShow(array('action_module'), 1);
//            $modules = DB::table('action')->distinct()->get('action_module')->toArray();
            if (!$modules) throw new Exception('查询modules出错，请联系管理员');
            foreach ($modules as $key => $value) {
                $arr[] = $value['action_module'];
            }

            // 数组key和value转换
            $arrKey = array_flip($arr);

            // 将一维数组的value替换为空的一维数组，使之成为二维数组
            foreach ($arrKey as $k5 => $v5) {
                $arr2[$k5][] = array();
            }
            $arrKey = $arr2;

            // 获取到action的id和中文名的名字
            $arrInfo = $this->actionModelObj->actionShow(array('action_id', 'action_name', 'action_module'));
//            $arrInfo = DB::table('action')->get(['action_id', 'action_name', 'action_module'])->toArray();
            if (!$arrInfo) throw new Exception('查询action的id和中文名出错，请联系管理员');

            foreach ($arrKey as $k => &$v) {
                foreach ($arrInfo as $key => $value) {
                    if ($k == $value['action_module']) {
                        array_push($v, $value);
                        unset($v[0]);
                    }
                }
            }
            $total = $this->actionModelObj->actionShow('', 1, 1);
            return array('code' => 200, 'message' => '成功', 'total' => $total, 'data' => $arrKey);
        } catch (Exception $e) {
            return array('code' => 400, 'message' => $e->getMessage());
        }

    }
}
