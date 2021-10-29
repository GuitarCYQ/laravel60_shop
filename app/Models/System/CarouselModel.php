<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CarouselModel extends Model
{
    //
    protected $table = 'carousel';
    //
    protected $primaryKey = 'carousel_id';
    // 表字段前缀
    protected $tablePrefix = 'carousel_';


    /**
     * 创建数据
     * @param array $row 数据
     * @return bool
     */
    public function create($row = array())
    {
        if (empty($row)) return false;
        $row[$this->tablePrefix . 'create_time'] = date('Y-m-d H:i:s');
        return self::query()->insert($row);
    }

    /**
     * 更新
     * @param array $row 修改的数据
     * @param int $id id值
     * @param string $fieldName 字段名
     * @return false|int
     */
    public function toUpdate($row = array(), $id = 0, $fieldName = '')
    {
        if (empty($row) || $id <= 0) return false;
        $fieldName = !empty($fieldName) ? $fieldName : $this->primaryKey;
        $row[$this->tablePrefix . 'update_time'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->where($fieldName, $id)->update($row);
    }

    /**
     * 依据条件获取
     * @param array $condition 条件
     * @param string $type 结果集
     * @param string $groupBy 集合
     * @param array $orderBy 排序
     * @param int $page 页数
     * @param int $pageSize 每页显示数
     * @return array|int
     */
    public function getByCondition($condition = array(), $type = '*', $groupBy = '', $orderBy = array(), $page = 0, $pageSize = 20)
    {
        DB::connection()->enableQueryLog();
        $query = self::query();

        if (isset($condition[$this->tablePrefix . 'title']) && !empty($condition[$this->tablePrefix . 'title'])) $query->where($this->tablePrefix . 'title', $condition[$this->tablePrefix . 'title']);
        if ($groupBy) $query->groupBy($groupBy);

        switch ($type) {
            case 'count(*)':
                $sql = $query->count();
                break;
            default:
                if ($orderBy) $query->orderBy(current($orderBy), end($orderBy));

                if ($page > 0 and $pageSize > 0) {
                    $start = ($page - 1) * $pageSize;
                    $query->offset($start)->limit($pageSize);
                }
                $sql = $query->get($type)->toArray();
                break;
        }
        //dd(DB::getQueryLog());
        return $sql;
    }
}
