<?php

namespace App\Http\Middleware;

use App\Models\System\ActionModel;
use App\Models\System\UserActionAuthorityModel;
use App\Models\System\UserTokenModel;
use Closure;
use Exception;
use Illuminate\Support\Facades\Auth;

class webToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        try {
            #token

            $token = trim($request->header('token'));
            $key = trim($request->header('key'));
            if (!$token || !$key) throw new Exception('未设置TOKEN或KEY');
            $userTokenObj = new UserTokenModel();
            $check = $userTokenObj->getByCondition(array('token' => $token, 'key' => $key));
            if (!$check)
            {
                throw new Exception('TOKEN或KEY不正确');
            }

            #action
            $user_id = $check[0]['user_id'];
            $uses = new UserActionAuthorityModel();
            $action_id = $uses->getByCondition(array('user_id' => $user_id));
            foreach ($action_id as $Key => $value)
            {
                $action_ids[] = $value['action_id'];
            }
            $actionStr = $uses->getActionName();

            $action = new ActionModel();
            if (!isset($action_ids) || !$action_ids) throw new \Exception('无此账号！');

            $sqlActionStr = $action->getByCondition(array('action_id' => $action_ids));
            foreach ($sqlActionStr as $key => $value)
            {
                $sqlActionStrs[] = $value['action_module']. '/' .$value['action_controllers']. '/' .$value['action_method'];
            }
            if (!in_array($actionStr,$sqlActionStrs)) {

                throw new Exception('您没有权限');
//                return response()->json(['code' => 401, 'message' => '您没有权限']);
            }
        } catch (Exception $e)
        {
            return response()->json(['code' => 401, 'message' => $e->getMessage()]);
        }
        return $next($request);
    }
}
