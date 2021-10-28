<?php


namespace App\Models\System;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserModel extends Model
{
    // 表名
    protected $table = 'user';
    // 主键
    protected $primaryKey = 'user_id';
    protected $hidden=['user_password'];

    /**
     * 创建
     * @param array $row    数据
     * @return bool
     */
    public function create($row = array()) {
        if (empty($row)) return false;
        $row[$this->table.'_create_time'] = date('Y-m-d H:i:s');
        return self::query()->insert($row);
    }

    /**
     * 更新数据
     * @param array $row    数据
     * @param int $value    值
     * @param string $fieldName 字段名
     * @return false|int
     */
    public function toUpdate($row = array(), $value = 0, $fieldName = '') {
        if (empty($row) || $value <= 0) return false;
        $fieldName = !empty($fieldName) ? $fieldName : $this->primaryKey;
        $row[$this->table.'_update_time'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->where($fieldName, $value)->update($row);
    }

    /**
     * 通过主键判断是否存在该记录
     * @param int $value    值
     * @return false|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function getByPrimaryKey($userId = 0) {
        if ($userId <= 0) return false;
        return self::query()->find($userId);
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
    public function getByCondition($condition = array(), $type = '*', $groupBy = '', $orderBy = array(), $page = 0, $pageSize = 20) {
        DB::connection()->enableQueryLog();
        $query = self::query();

        if (isset($condition['user_id']) && !empty($condition['user_id'])) {
            if (is_array($condition['user_id'])) {
                $query->whereIn('user_id', $condition['user_id']);
            } else {
                $query->where('user_id', $condition['user_id']);
            }
        }
        if (isset($condition['user_name']) && !empty($condition['user_name'])) $query->where('user_name', $condition['user_name']);
        if (isset($condition['user_name_en']) && !empty($condition['user_name_en'])) $query->where('user_name_en', $condition['user_name_en']);
        if (isset($condition['user_phone']) && !empty($condition['user_phone'])) $query->select('role_id')->where('user_phone', $condition['user_phone']);
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
                if ($orderBy) $query->orderBy(current($orderBy), end($orderBy));

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

    /**
     * 删除
     * @param int $value    值
     * @param string $fieldName 字段名
     * @return false|int
     */
    public function toDelete($value = 0, $fieldName = '') {
        if ($value <= 0) return false;
        $fieldName = !empty($fieldName) ? $fieldName : $this->primaryKey;
        return DB::table($this->table)->where($fieldName, $value)->delete();
    }
}
