<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShopProductStockModel extends Model
{
    //表名
    protected $table = 'shop_product_stock';

    //主键
    protected $primaryKey = 'sps_id';


    /**
     * 创建
     * @param array $row 数据
     * @return bool
     */
    public function toCreate($row = array())
    {
        if (empty($row)) return false;
        $row['create_time'] = date('Y-m-d H:i:s');
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
        return DB::table($this->table)->where($fieldName, $value)->update($row);
    }

    //总数
    public function total()
    {
        return self::query()->count();
    }

    /**
     * 依据条件获取
     * @param array $condition
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

        if (isset($condition['sps_id']) && !empty($condition['sps_id'])) {
            if (is_array($condition['sps_id'])) {
                $query->whereIn('sps_id', $condition['sps_id']);
            } else {
                $query->where('sps_id', $condition['sps_id']);
            }
        }
        if (isset($condition['code']) && !empty($condition['code'])) $query->where('code', $condition['code']);
        if (isset($condition['status']) && $condition['status'] !== '') $query->where('shop_status', $condition['status']);

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
