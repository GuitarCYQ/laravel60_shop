<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\LogoutRequest;
use App\Models\System\UserTokenModel;
use App\Services\Admin\System\LoginService;
use Illuminate\Http\Request;

class LoginController extends Controller
{

    public function login(LoginRequest $request)
    {
        $row = [
            'user_phone' => trim($request->get('phone')),
            'user_password' => md5(trim($request->get('password')))
        ];
        $obj = new LoginService();
        return response()->json($obj->login($row));
    }

    public function logout(Request $request)
    {
        $row  = [
            'token' => trim($request->header('token')),
            'key' => trim($request->header('key')),
        ];
        $obj = new LoginService();
        return response()->json($obj->logout($row));
    }
}
