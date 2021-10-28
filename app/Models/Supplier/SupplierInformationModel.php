<?php

namespace App\Models\Supplier;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SupplierInformationModel extends Model
{
    protected $table = 'supplier_information';

    //主鍵
    protected $primaryKey = 'si_id';

    /**
     * 创建
     * @param array $row 数据
     * @return bool
     */
    public function toCreate($row = array())
    {
        if (empty($row)) return false;
        $row['create_time'] = date('Y-m-d H:i:s');
        return self::query()->insert($row);
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

        if (isset($condition['si_id']) && !empty($condition['si_id'])) {
            if (is_array($condition['si_id'])){
                $query->whereIn('si_id', $condition['si_id']);
            } else {
                $query->where('si_id', $condition['si_id']);
            }
        }
        if (isset($condition['supplier']) && !empty($condition['supplier'])) $query->where('supplier_id', $condition['supplier']);
        if (isset($condition['id_number']) && !empty($condition['id_number'])) $query->where('id_number', $condition['id_number']);
        if (isset($condition['id_type']) && $condition['id_type'] !== '') $query->where('id_type', $condition['id_type']);

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
