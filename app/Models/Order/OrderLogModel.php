<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;

class OrderLogModel extends Model
{
    //表名
    protected $table = 'order_log';

    //主鍵
    protected $primaryKey = 'ol_id';

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
