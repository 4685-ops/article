<?php


namespace app\api\controller\v1;


use app\api\service\TokenService;
use think\Controller;

class BaseController extends Controller
{
    public function checkPrimaryScope(){
        TokenService::needPrimaryScope();
    }

    public function checkExclusiveScope(){
        TokenService::needExclusiveScope();
    }
}