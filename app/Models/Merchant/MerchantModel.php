<?php

namespace App\Models\Merchant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MerchantModel extends Model
{
    //表名
    protected $table = 'merchant';

    //主键
    protected $primaryKey = 'merchant_id';

    protected $hidden = ['merchant_password'];


    /**
     * 创建
     * @param array $row 数据
     * @return bool
     */
    public function toCreate($row = array())
    {
        if (empty($row)) return false;
        $row['merchant_create_time'] = date('Y-m-d H:i:s');
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
        $row['merchant_update_time'] = date('Y-m-d H:i:s');
        return DB::table($this->table)->where($fieldName, $value)->update($row);
    }

    /**
     * 总数
     */
    public function total()
    {
        return self::query()->count();
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

        if (isset($condition['merchant_id']) && !empty($condition['merchant_id']))
        {
            if (is_array($condition['merchant_id'])) {
                $query->whereIn('merchant_id', $condition['merchant_id']);
            } else {
                $query->where('merchant_id', $condition['merchant_id']);
            }
        }
        if (isset($condition['merchant_name']) && !empty($condition['merchant_name'])) $query->where('merchant_name', $condition['merchant_name']);
        if (isset($condition['merchant_name_en']) && !empty($condition['merchant_name_en'])) $query->where('merchant_name_en', $condition['merchant_name_en']);
        if (isset($condition['name']) && !empty($condition['name'])) {
            $query->where(function ($query) use ($condition) {
                $query->orWhere('merchant_name', $condition['name'])->orWhere('merchant_name_en', 'like', $condition['name']);
            });
        }
        if (isset($condition['status']) && $condition['status'] !== '') $query->where('user_status', $condition['status']);
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
