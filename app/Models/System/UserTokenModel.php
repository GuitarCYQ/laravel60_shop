<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserTokenModel extends Model
{
    //表名
    protected $table = 'user_token';

    //主键
    protected $primaryKey = 'ut_id';

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


        if (isset($condition['user_id']) && !empty($condition['user_id'])) $query->where('user_id', $condition['user_id']);
        if (isset($condition['key']) && !empty($condition['key'])) $query->where('key', $condition['key']);
        if (isset($condition['token']) && !empty($condition['token'])) $query->where('token', $condition['token']);

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

    public function logout($user_id)
    {
        if (empty($user_id)) return false;
        return self::query()->where('user_id', $user_id)->delete();
    }

    // 删除过期的记录
    public function delToken()
    {
        DB::beginTransaction();
        DB::enableQueryLog();
        $query = self::query();
        $ret = $query->select($this->primaryKey, 'create_time')->get()->toArray();
        if (!$ret) DB::rollBack();
        // 获取当前时间戳
        $nowTime = time();
        foreach ($ret as $value) {
            // 将数据库中的记录转为时间戳
            $tokenTime = strtotime($value['create_time']);
            $existTime = $nowTime - $tokenTime;

            // 如果token存在时间大于该值2*60*60，则从数据库中删除
            if ($existTime >= 2*60*60){
                $t = $query->where($this->primaryKey, $value[$this->primaryKey])->delete();
//                dump(DB::getQueryLog());
                if (!$t) DB::rollBack();
                DB::commit();
            }
        }
    }

}
