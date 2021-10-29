<?php

namespace App\Models\System\Authority;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MenuAuthorityModel extends Model
{
    protected $table = 'role_menu_authority';
    protected $primaryKey = 'rma_id';


    /**
     * 创建
     * @param array $row    数据
     * @return bool
     */
    public function create($row = array()) {
        if (empty($row)) return false;
        return self::query()->insert($row);
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
    public function getByCondition($condition = array(), $type = '*', $groupBy = '', $orderBy = ['','desc'], $page = 0, $pageSize = 20) {
        //表中的字段前缀
        $tbFieldPre = 'role_';

        DB::connection()->enableQueryLog();
        $query = self::query();
        if (isset($condition[$tbFieldPre.'id']) && $condition[$tbFieldPre.'id'] > 0) {
            if (is_array($condition[$tbFieldPre.'id'])) {
                $query->whereIn($tbFieldPre.'id', $condition[$tbFieldPre.'id']);
            } else {
                $query->select('menu_id')->where($tbFieldPre.'id',  $condition[$tbFieldPre.'id']);
            }
        }

        //dd(DB::getQueryLog());
        if ($groupBy) $query->groupBy($groupBy);

        switch ($type) {
            case 'count(*)':
                $sql = $query->count();
                break;
            default:
                if ($orderBy[0]){
                    if ($orderBy[1] == 'asc') {
                        $query->orderBy(current($orderBy), end($orderBy));
                    } else {
                        $query->orderByDesc($orderBy[0]);
                    }
                }
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

}
