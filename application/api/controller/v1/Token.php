<?php


namespace app\api\controller\v1;


use app\api\service\TokenService;
use app\api\validate\TokenEmptyValidate;
use app\api\validate\TokenValidate;
use think\Controller;
use think\Request;

class Token extends Controller
{

    /**
     * @function   getToken 得到code获取token
     *
     * @param string $code
     * @return \think\response\Json
     * @throws \app\lib\exception\ParameterException
     * @throws \think\Exception
     * @author admin
     *
     * @date 2019/4/29 11:18
     */
    public function getToken($code = '')
    {
        //检查code是否存在
        (new TokenValidate())->goCheck();

        $tokenService = new TokenService($code);

        $data['token'] = $tokenService->get();

        return $this->successData(200, $data);
    }

    /**
     * @function   getVerifyToken   检查token是否存在
     *
     * @param Request $request
     * @return \think\response\Json
     * @throws \app\lib\exception\ParameterException
     * @author admin
     *
     * @date 2019/4/29 11:18
     */
    public function getVerifyToken(Request $request)
    {

        (new TokenEmptyValidate())->goCheck();


        $result = TokenService::getVerifyToken($request->post('token'));

        return $this->successData(200, $result);

    }

    /**
     * @function   successData  返回成功的json格式数据
     *
     * @param int $errorCode
     * @param array $data
     * @param string $msg
     * @return \think\response\Json
     * @author admin
     *
     * @date 2019/5/6 10:52
     */
    public function successData($errorCode = 200, $data = [], $msg = '')
    {
        $data = [
            'errorCode' => $errorCode,
            'data' => $data,
            'msg' => $msg
        ];

        return json($data);
    }
}