<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class UserActionAuthorityModel extends Model
{
    //表名
    protected $table = 'user_action_authority';

    //主键
    protected $primaryKey = 'uac_id';

    public function getActionName()
    {
        list($actionStr, $method) = explode('@',\request()->route()->getActionName());

        #模块名
        $modules = str_replace(
            '\\',
            '.',
            str_replace(
                'App\\Http\\Controllers\\Admin\\',
                '',
                trim(
                    implode('\\', array_slice(explode('\\', $actionStr), 0, -1)),
                    '\\'
                )
            )
        );

        #控制器名
        $controller = str_replace(
            '',
            '',
            substr(strrchr($actionStr, '\\'),1)
        );

        return $modules.'/'.$controller.'/'.$method;

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
    public function getByCondition($condition = array(), $type = '*', $groupBy = '', $orderBy = array(), $page = 0, $pageSize = 20) {
        DB::connection()->enableQueryLog();
        $query = self::query();

        if (isset($condition['user_id']) && !empty($condition['user_id'])) $query->where('user_id', $condition['user_id']);
        if (isset($condition['action_id']) && !empty($condition['action_id'])) $query->where('action_id', $condition['action_id']);

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
//        dd(DB::getQueryLog());
        return $sql;
    }
}
