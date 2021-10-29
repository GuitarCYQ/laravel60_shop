<?php


namespace App\Services\Admin\System;


use App\Models\System\CarouselModel;
use Exception;
use Illuminate\Support\Facades\DB;

class CarouselService
{
    protected $CarouselModelObj;
    protected $CarouselInfo;

    public function __construct($nameArr = array())
    {
        $this->CarouselModelObj = new CarouselModel();
        if ($nameArr) {
            $this->CarouselInfo = $this->CarouselModelObj->getByCondition($nameArr);
        }
    }

    /**
     * 创建或更新
     * @param array $row    数据
     * @param int $id   ID
     * @return array
     */
    public function createOrUpdate($row = array(), $id = 0)
    {
        //数据表中的字段前缀
        $tableName = 'carousel_';
        //错误信息的前缀
        $messagePrefix = '轮播';
        DB::beginTransaction();
        try {
            if (empty($row))  throw new Exception('数据不能为空！');
            if (empty($row['carousel_title'])) throw new \Exception('title不能为空！');
            if (empty($row['carousel_target'])) throw new \Exception('目标链接不能为空！');
            if (empty($row['carousel_imgs']) ) throw new Exception('图片路径不能为空！');
            if (empty($row['carousel_sort']) && $row['carousel_sort'] < 0) throw new Exception('排序不能为空！');
            if (empty($row['carousel_status']) && $row['carousel_status'] < 0) throw new Exception('状态不能为空！');

            if ($id > 0) {
                $isExistId = $this->CarouselModelObj->find($id); //find()为成员方法
                if (!$isExistId) throw new Exception($messagePrefix.'ID不存在！');
                if (!$this->CarouselModelObj->toUpdate($row, $id)) throw new Exception($messagePrefix.'更新失败！');
                $typeStr = '更新';
            } else {
                //判断title是否存在
                $nameArr = array('carousel_title' => $row['carousel_title']);
                $this->__construct($nameArr);
                if ($this->CarouselInfo) throw new Exception($messagePrefix.'已存在，无需创建！');
                if (!$this->CarouselModelObj->create($row)) throw new Exception($messagePrefix.'创建失败！');
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
