<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LogisticsModel extends Model
{
    //表名
    protected $table = 'logistics';

    //主鍵
    protected $primaryKey = 'logistics_id';

    /**
     * 创建
     * @param array $row 数据
     * @return bool
     */

    public function createGetLogisticsId($row = array()) {
        if (empty($row)) return false;
        $row['logistics_create_time'] = date('Y-m-d H:i:s');
        return self::query()->insertGetId($row);
    }


    /**
     * 更新
     * @param array $row 数据
     * @param int $value 值
     * @param string $fieldName 字段名
     * @return false|int
     */
    public function toUpdate($row = array(), $value = 0, $fieldName = '')
    {
        if (empty($row) || $value <= 0) return false;
        $fieldName = !empty($fieldName) ? $fieldName : $this->primaryKey;
        $row['logistics_update_time'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->where($fieldName, $value)->update($row);
    }

    //总数
    public function total()
    {
        return self::query()->count();
    }

    /**
     * 依据条件获取
     * @param array $coundition 条件
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

        if (isset($condition['logistics_id']) && !empty($condition['logistics_id'])) {
            if (is_array($condition['logistics_id'])){
                $query->whereIn('logistics_id', $condition['logistics_id']);
            } else {
                $query->where('logistics_id', $condition['logistics_id']);
            }
        }
        if (isset($condition['logistics_code']) && !empty($condition['logistics_code'])) $query->where('logistics_code', $condition['logistics_code']);
        if (isset($condition['order_code']) && !empty($condition['order_code'])) $query->where('order_code', $condition['order_code']);

        if (isset($condition['status']) && $condition['status'] !== '') $query->where('supplier_status', $condition['status']);

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

        return $sql;
    }

}
