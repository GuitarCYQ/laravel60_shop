<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogisticsRequest;
use App\Services\Admin\Logistics\LogisticsService;
use Illuminate\Http\Request;

class LogisticsController extends Controller
{
    //创建或修改
    public function createOrUpdate(LogisticsRequest $request)
    {
        $row = array(
            'logistics_code' =>  trim($request->input('code', '')),
            'order_code' =>  trim($request->input('order_code', '')),
            'order_quantity' =>  trim($request->input('order_quantity', 1)),
            'ec_id' =>  trim($request->input('ec', 0)),
            'logistics_freight' =>  trim($request->input('freight', '0.00')),
            'logistics_status' =>  trim($request->input('status', '1')),
        );
        $Id = $request->input('id', 0);

        $obj = new LogisticsService();
        return response()->json($obj->createOrUpdate($row, $Id));
    }

    //列表
    public function list(Request $request)
    {
        $code = trim($request->input('code', ''));
        $status =  trim($request->input('status', ''));
        $page =  trim($request->input('page', 0));
        $pageSize =  trim($request->input('page_size', 10));

        $obj = new LogisticsService();
        return response()->json($obj->list($code, $status, $page, $pageSize));
    }

    //获取快递公司
    public function getEc()
    {
        $obj = new LogisticsService();
        return response()->json($obj->getCpmpany());
    }

    /**
     *@function: CURL模拟POST 請求
     *@Author:   xurui[xuruiss@126.com]
     *@DateTime: 2017-03-08 am
     *@Param:    url string  接口地址
     */
    public static function post_curl ($url) {
        //优先使用curl模式发送数据
        if (function_exists('curl_init') == 1){
            $curl = curl_init();
            curl_setopt ($curl, CURLOPT_URL, $url);
            curl_setopt ($curl, CURLOPT_HEADER,0);
            curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
            curl_setopt ($curl, CURLOPT_TIMEOUT,5);
            $get_content = curl_exec($curl);
            curl_close ($curl);
        } else {
            $snoopy = new Snoopy();
            $snoopy->referer = 'http://www.baidu.com/';//伪装来源
            $snoopy->fetch($url);
            $get_content = $snoopy->results;
        }
        return $get_content;
    }


    /**
     * function: 物流信息
     * @Author   :xurui
     * @DateTime 2017-03-08 am
     * @param  $company_code string 快递公司编码 公司名全拼且都小写： 例如：顺丰速递 => shunfeng ;圆通快递 => yuantong等
     * @param  $express_number string   快递单号  一串数字组成的快递单号
     * @param  $is_newest    int  0:查询全部快递信息;1:查询查询最新一条快递动态信息
     * @return  json
     */
    public static function getLogisticsSearch($company_code,$express_number,$is_newest = 0) {
        $appkey = env('APP_KEYS','');//快递100授权秘钥
        $customerCode = env('CUSTOMER_CODE','');//快递100客户code
        $expressCodeUrl = env('GET_EXPRESS_CODE_URL','');//快递公司编码接口地址
        $expressListUrl = env('GET_EXPRESS_LIST_URL');//快递100的快递列表接口
        $url_company_code = $expressCodeUrl.'?num='.$express_number.'&key='.$appkey;//单号归属公司智能判断接口
        $arr_company_code = json_decode(file_get_contents($url_company_code), true); //获得实时的快递公司的编号
        $company_code = $arr_company_code[0]['comCode'] ?: $company_code; //快递公司对应的编码

        $post_data = array();
        $post_data["customer"] = $customerCode;
        $post_data["param"] = '{"com":"'.strtolower($company_code).'","num":"'.$express_number.'"}';
        $url_express_info = $expressListUrl;
        $post_data["sign"] = strtoupper(md5($post_data["param"].$appkey.$post_data["customer"]));
        $url_param = "";
        foreach ($post_data as $k => $v) {
            $url_param .= "$k=".urlencode($v)."&";//默认UTF-8编码格式
        }
        $post_data = substr($url_param, 0, -1);
        unset($url_param);
        $powered = '查询数据由：<a href="http://kuaidi100.com" target="_blank">KuaiDi100.Com （快递100）</a> 网站提供 '; ////请勿删除变量$powered 的信息，否者本站将不再为你提供快递接口服务。
        $express_info_json = self::post_curl($url_express_info.'?'.$post_data);
        $express_info_arr = json_decode($express_info_json, true);

        if ($is_newest == 1) {
            $data_tmp = [];
            foreach($express_info_arr['data'] as $k=>$v){
                $data_tmp[] = $v;
                break;
            }
            $express_info_arr['data'] = $data_tmp;
        }
        $express_info_arr['state'] = self::getState($express_info_arr['state']);
        return $express_info_arr;
        /*
        $obj = new ComController();
        if ($express_info_arr['status'] != 200) {echo $obj->json('400',array(),$express_info_arr['message']);exit;}
        if ($express_info_arr['status'] == 200) {
            if ($is_newest == 1) { //返回最新的快递实时信息
                $express_info_arr['newest_info'] = $express_info_arr['data']['0'];
                unset($express_info_arr['data']);
            }
            return $obj ->json(200,$express_info_arr,'获取最新快递信息成功') ;
        }
        */
    }
}
