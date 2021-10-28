<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ShopCollectionModel extends Model
{
    protected $table = 'shop_collection';

    protected $primaryKey = 'sc_id';

    public function toCreate($row = array())
    {
        if (empty($row)) return false;
        $row['create_time'] = date('Y-m-d H:i:s');
        return self::query()->insert($row);
    }

    public function toUpdate($row = array(), $value = 0, $fieldName = '')
    {
        if (empty($row) || $value <= 0) return false;
        $fieldName = !empty($fieldName) ? $fieldName : $this->primaryKey;
        $row['update_time'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->where($fieldName, $value)->update($row);
    }



    /**
     * 查询
     * @param array $condition
     * @param string $type 结果集
     * @param string $groupBy 集合
     * @param array $orderBy 排序
     * @param int $page 页数
     * @param int $pageSize 每页显示数
     * @return array|int
     */
    public function getByCondition( $condition = array(),  $type = '*',  $groupBy = '',  $orderBy = array(),  $page = 0,  $pageSize = 20)
    {
        DB::connection()->enableQueryLog();
        $query = self::query();

        if (isset($condition['sc_id']) && !empty($condition['sc_id']))
        {
            if (is_array($condition['sc_id'])) {
                $query->whereIn('sc_id', $condition['sc_id']);
            } else {
                $query->where('sc_id', $condition['sc_id']);
            }
        }
        if (isset($condition['name']) && !empty($condition['name'])) $query->where('name', $condition['name']);
        if (isset($condition['code']) && !empty($condition['code'])) $query->where('code', $condition['code']);
        if (isset($condition['status']) && $condition['status'] !== '') $query->where('status', $condition['status']);
        if ($groupBy) $query->groupBy($groupBy);

        switch ($type) {
            case 'count(*)':
                $sql = $query->count();
                break;
            default:
                if ($orderBy) $query->orderBy(current($orderBy), end($orderBy));

                if ($page > 0 and $pageSize > 0)
                {
                    $start = ($page - 1) * $pageSize;
                    $query->offset($start)->limit($pageSize);
                }

                $sql = $query->get($type)->toArray();
                break;
        }

        return  $sql;
    }
}
