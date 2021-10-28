<?php

namespace App\Models\Logistics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EcModel extends Model
{
    //表名
    protected $table = 'express_company';

    //主鍵
    protected $primaryKey = 'ec_id';

    /**
     * 查询地方
     */
    public function getCpmpany($country = array(), $type = '*')
    {
        DB::connection()->enableQueryLog();
        $query = self::query();

        if (isset($country['ec_id']) && $country['ec_id'] !== '') $query->where('ec_id', $country['ec_id']);
        if (isset($country['country_parent_id']) && $country['country_parent_id'] !== '') $query->where('country_parent_id', $country['country_parent_id']);
        $sql = $query->get($type)->toArray();
//        print_r(DB::getQueryLog());
//        die();
        return $sql;


    }
}
