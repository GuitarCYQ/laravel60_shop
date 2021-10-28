<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShopModel extends Model
{
    //表名
    protected $table = 'shop';

    //主键
    protected $primaryKey = 'shop_id';


    /**
     * 创建
     * @param array $row 数据
     * @return bool
     */
    public function createGetShopId($row = array())
    {
        if (empty($row)) return false;
        $row['shop_create_time'] = date('Y-m-d H:i:s');
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
        $row['shop_update_time'] = date('Y-m-d H:i:s');
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

        if (isset($condition['shop_id']) && !empty($condition['shop_id'])) {
            if (is_array($condition['shop_id'])) {
                $query->whereIn('shop_id', $condition['shop_id']);
            } else {
                $query->where('shop_id', $condition['shop_id']);
            }
        }
        if (isset($condition['shop_name']) && !empty($condition['shop_name'])) $query->where('shop_name', $condition['shop_name']);
        if (isset($condition['shop_name_en']) && !empty($condition['shop_name_en'])) $query->where('shop_name_en', $condition['shop_name_en']);
        if (isset($condition['name']) && !empty($condition['name'])) {
            $query->where(function ($query) use ($condition) {
                $query->orWhere('shop_name', $condition['name'])->orWhere('shop_name_en', $condition['name']);
            });
        }
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
