<?php


namespace app\api\controller\v1;


use app\api\service\TokenService;
use app\api\validate\TokenValidate;
use think\Cache;
use think\Controller;

class Token extends Controller
{

    public function getToken($code = '')
    {

        //检查code是否存在
        (new TokenValidate())->goCheck();

        $tokenService = new TokenService($code);

        $token = $tokenService->get();

        var_export($token);
    }

}