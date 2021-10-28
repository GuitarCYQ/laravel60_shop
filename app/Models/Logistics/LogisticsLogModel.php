<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;

class LogisticsLogModel extends Model
{
    //表名
    protected $table = 'logistics_log';

    //主鍵
    protected $primaryKey = 'll_id';

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
}
