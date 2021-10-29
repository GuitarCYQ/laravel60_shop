<?php

namespace App\Models\Merchant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MerchantTokenModel extends Model
{
    //表名
    protected $table = 'merchant_token';

    //主键
    protected $primaryKey = 'mt_id';

    /**
     * 创建
     * @param array $row 数据
     * @return bool
     */
    public function create($row = array())
    {
        if (empty($row)) return false;
        $row['create_time'] = date('Y-m-d H:i:s');
        return self::query()->insert($row);
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


        if (isset($condition['merchant_id']) && !empty($condition['merchant_id'])) $query->where('merchant_id', $condition['merchant_id']);
        if (isset($condition['m_key']) && !empty($condition['m_key'])) $query->where('m_key', $condition['m_key']);
        if (isset($condition['m_token']) && !empty($condition['m_token'])) $query->where('m_token', $condition['m_token']);

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
//        print_r(DB::getQueryLog());
//		die();
        return  $sql;
    }

    public function logout($merchant_id)
    {
        if (empty($merchant_id)) return false;
        return self::query()->where('merchant_id', $merchant_id)->delete();
    }
}
