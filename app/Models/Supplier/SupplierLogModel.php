<?php

namespace App\Models\Supplier;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SupplierLogModel extends Model
{
    //表名
    protected $table = 'supplier_log';

    //主鍵
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
