<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductModel extends Model
{
    protected $table = 'product';

    protected $primaryKey = 'product_id';

    public function toCreate($row = array())
    {
        if (empty($row)) return false;
        $row['product_create_time'] = date('Y-m-d H:i:s');
        return self::query()->insert($row);
    }

    public function toUpdate($row = array(), $value = 0, $fieldName = '')
    {
        if (empty($row) || $value <= 0) return false;
        $fieldName = !empty($fieldName) ? $fieldName : $this->primaryKey;
        $row['product_update_time'] = date('Y-m-d H:i:s');
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

        if (isset($condition['product_id']) && !empty($condition['product_id']))
        {
            if (is_array($condition['product_id'])) {
                $query->whereIn('product_id', $condition['product_id']);
            } else {
                $query->where('product_id', $condition['product_id']);
            }
        }
        if (isset($condition['product_name']) && !empty($condition['product_name'])) $query->where('product_name', $condition['product_name']);
        if (isset($condition['product_name_en']) && !empty($condition['product_name_en'])) $query->where('product_name_en', $condition['product_name_en']);
        if (isset($condition['name']) && !empty($condition['name'])) {
            $query->where(function ($query) use ($condition) {
                $query->orWhere('product_name', $condition['name'])->orWhere('product_name_en', $condition['name']);
            });
        }
        if (isset($condition['product_sku']) && !empty($condition['product_sku'])) $query->where('product_sku', $condition['product_sku']);
        if (isset($condition['status']) && $condition['status'] !== '') $query->where('product_status', $condition['status']);
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
