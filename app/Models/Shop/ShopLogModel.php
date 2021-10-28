<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;

class ShopLogModel extends Model
{
    //表名
    protected $table = 'shop_log';

    //主键
    protected $primaryKey = 'sl_id';


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
}
