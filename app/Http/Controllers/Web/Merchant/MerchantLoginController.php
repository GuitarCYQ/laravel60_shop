<?php

namespace App\Http\Controllers\Web\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantLoginRequest;
use App\Services\Admin\Merchant\MerchantService;
use Illuminate\Http\Request;

class MerchantLoginController extends Controller
{
    //登录
    public function login(MerchantLoginRequest $request)
    {
        $row = [
            'merchant_phone'    =>  trim($request->input('phone','')),
            'merchant_password'    =>  md5(trim($request->input('password','')))
        ];

        $MerchantObj = new MerchantService();
        return response()->json($MerchantObj->login($row));
    }

    //退出
    public function logout(Request $request)
    {
        $row  = [
            'm_token' => trim($request->header('m_token')),
            'm_key' => trim($request->header('m_key')),
        ];
        $obj = new MerchantService();
        return response()->json($obj->logout($row));
    }
}
