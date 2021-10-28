<?php

namespace App\Models\Supplier;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CountryModel extends Model
{
    //表名
    protected $table = 'country';

    //主鍵
    protected $primaryKey = 'country_id';

    /**
     * 查询地方
     */
    public function getplaceList($country = array(), $type = '*')
    {
        DB::connection()->enableQueryLog();
        $query = self::query();

        if (isset($country['country_id']) && $country['country_id'] !== '') $query->where('country_id', $country['country_id']);
        if (isset($country['country_parent_id']) && $country['country_parent_id'] !== '') $query->where('country_parent_id', $country['country_parent_id']);
        $sql = $query->get($type)->toArray();
//        print_r(DB::getQueryLog());
//        die();
        return $sql;


    }
}
