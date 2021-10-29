<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MenuModel extends Model
{
    //表名
    protected $table = 'menu';
    //主键id
    protected $primaryKey = 'menu_id';
    //
    protected $createTime = 'menu_create_time';
    //
    protected $updateTime = 'menu_update_time';


    /**
     * 创建
     * @param array $row    数据
     * @return bool
     */
    public function create($row = array()) {
        if (empty($row)) return false;
        $row[$this->createTime] = date('Y-m-d H:i:s');
        return self::query()->insert($row);
    }

    /**
     * 更新
     * @param array $row    修改的数据
     * @param int $id    id值
     * @param string $fieldName 字段名
     * @return false|int
     */
    public function toUpdate($row = array(), $id = 0, $fieldName = '') {
        if (empty($row) || $id <= 0) return false;
        $fieldName = !empty($fieldName) ? $fieldName : $this->primaryKey;
        $row[$this->updateTime] = date('Y-m-d H:i:s');
        return DB::table($this->table)->where($fieldName, $id)->update($row);
    }

    /**
     * 依据条件获取
     * @param array $condition  条件
     * @param string $type  结果集
     * @param string $groupBy   集合
     * @param array $orderBy    排序
     * @param int $page 页数
     * @param int $pageSize 每页显示数
     * @return array|int
     */
    public function getByCondition($condition = array(), $type = '*', $groupBy = '', $orderBy = ['','desc'], $page = 0, $pageSize = 20) {
        //表中的字段前缀
        $tbFieldPre = 'menu_';

        DB::connection()->enableQueryLog();
        $query = self::query();

        if (isset($condition[$tbFieldPre.'status']) && !empty($condition[$tbFieldPre.'status'])) $query->where($tbFieldPre.'status', $condition[$tbFieldPre.'status']);

        if (isset($condition[$tbFieldPre.'parent_id']) && $condition[$tbFieldPre.'parent_id'] >= 0) {
            if (is_array($condition[$tbFieldPre.'parent_id'])) {
                $query->whereIn($tbFieldPre.'parent_id', $condition[$tbFieldPre.'parent_id']);
            } else {
                $query->select($tbFieldPre.'id', $tbFieldPre.'name')->where($tbFieldPre.'parent_id',  $condition[$tbFieldPre.'parent_id']);
            }
        }
        if (isset($condition[$tbFieldPre.'id']) && $condition[$tbFieldPre.'id'] >= 0) {
            if (is_array($condition[$tbFieldPre.'id'])) {
                $query->whereIn($tbFieldPre.'id', $condition[$tbFieldPre.'id']);
            } else {
                $query->where($tbFieldPre.'id',  $condition[$tbFieldPre.'id']);
            }
        }

        //dd(DB::getQueryLog());


        if (isset($condition[$tbFieldPre.'name']) && !empty($condition[$tbFieldPre.'name'])) $query->where($tbFieldPre.'name', $condition[$tbFieldPre.'name']);
        if (isset($condition[$tbFieldPre.'name_en']) && !empty($condition[$tbFieldPre.'name_en'])) $query->orwhere($tbFieldPre.'name_en', $condition[$tbFieldPre.'name_en']);

        if (isset($condition['name']) && !empty($condition['name'])) {
            $query->where(function ($query) use ($condition) {
                $query->orWhere('user_name', $condition['name'])->orWhere('user_name_en', $condition['name']);
            });
        }
        if ($groupBy) $query->groupBy($groupBy);

        switch ($type) {
            case 'count(*)':
                $sql = $query->count();
                break;
            default:
                if ($orderBy[0]){
                    if ($orderBy[1] == 'asc') {
                        $query->orderBy(current($orderBy), end($orderBy));
                    } else {
                        $query->orderByDesc($orderBy[0]);
                    }
                }
                if ($page > 0 and $pageSize > 0) {
                    $start = ($page - 1) * $pageSize;
                    $query->offset($start)->limit($pageSize);
                }
                $sql = $query->get($type)->toArray();
                break;
        }
//		dd(DB::getQueryLog());
        return $sql;
    }


}
