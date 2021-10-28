<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserLoginModel extends Model
{
    // 表名
    protected $table = 'user';
    // 主键
    protected $primaryKey = 'user_id';

    public function getByCondition($condition = array(), $type = '*', $groupBy = '', $orderBy = array(), $page = 0, $pageSize = 20) {
        DB::connection()->enableQueryLog();
        $query = self::query();

        if (isset($condition['user_id']) && !empty($condition['user_id'])) {
            if (is_array($condition['user_id'])) {
                $query->whereIn('user_id', $condition['user_id']);
            } else {
                $query->where('user_id', $condition['user_id']);
            }
        }
        if (isset($condition['user_name']) && !empty($condition['user_name'])) $query->where('user_name', $condition['user_name']);
        if (isset($condition['user_name_en']) && !empty($condition['user_name_en'])) $query->where('user_name_en', $condition['user_name_en']);
        if (isset($condition['name']) && !empty($condition['name'])) {
            $query->where(function ($query) use ($condition) {
                $query->orWhere('user_name', $condition['name'])->orWhere('user_name_en', $condition['name']);
            });
        }
        if (isset($condition['user_phone']) && $condition['user_phone'] !== '') $query->where('user_phone', $condition['user_phone']);
        if (isset($condition['type']) && $condition['type'] !== '') $query->where('user_type', $condition['type']);
        if (isset($condition['status']) && $condition['status'] !== '') $query->where('user_status', $condition['status']);
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
//		dd(DB::getQueryLog());
        return $sql;
    }
}
