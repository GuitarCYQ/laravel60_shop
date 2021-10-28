<?php

namespace App\Models\Supplier;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SupplierModel extends Model
{
    //表名
    protected $table = 'supplier';

    //主鍵
    protected $primaryKey = 'supplier_id';

//    /**
//     * 创建
//     * @param array $row 数据
//     * @return bool
//     */
//    public function create($row = array())
//    {
//        if (empty($row)) return false;
//        $row['supplier_create_time'] = date('Y-m-d H:i:s');
//        return self::query()->insert($row);
//    }

    /**
     * 创建并获取ID
     * @param array $row    数据
     * @return false|int
     */
    public function createGetSupplierId($row = array()) {
        if (empty($row)) return false;
        $row['supplier_create_time'] = date('Y-m-d H:i:s');
        return self::query()->insertGetId($row);
    }

//    /**
//     * 通过主键获取
//     * @param int $value 值
//     * @return false|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
//     */
//    public function getByPrimaryKey($value = 0)
//    {
//        if ($value <= 0) return false;
//        return self::query()->find($value)->toArray();
//    }

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

        if (isset($condition['supplier_id']) && !empty($condition['supplier_id'])) {
            if (is_array($condition['supplier_id'])){
                $query->whereIn('supplier_id', $condition['supplier_id']);
            } else {
                $query->where('supplier_id', $condition['supplier_id']);
            }
        }
        if (isset($condition['supplier_name']) && !empty($condition['supplier_name'])) $query->where('supplier_name', $condition['supplier_name']);
        if (isset($condition['name']) && !empty($condition['name'])) {
            $query->where(function ($query) use ($condition) {
                $query->where('supplier_name', $condition['name']);
            });
        }
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

    /**
     * 删除
     * @paran int $value 值
     * @param string $fieldName 字段名
     * @retuen false|int
     */
    public function toDelete($value = 0, string $fieldName = '')
    {
        if ($value <= 0) return false;
        $fieldName = !empty($fieldName) ? $fieldName : $this->primaryKey;
        return DB::table($this->table)->where($fieldName, $value)->delete();
    }



}
