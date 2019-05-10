<?php


namespace app\api\controller\v1;


use app\api\model\User;
use app\api\service\TokenService as tokenService;
use app\api\validate\AddressValidate;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;

class Address extends BaseController
{
    /**
     * @var array  thinkphp5的前置方法
     */
    protected $beforeActionList = [
        'checkPrimaryScope' => [
            'only' => 'createuseraddress'
        ]
    ];

    /**
     * @function   createUserAddress    这个地方不好 用户的地址设计成1对1了 以后有时间需要修改
     *
     * @throws SuccessMessage
     * @throws UserException
     * @throws \app\lib\exception\ParameterException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author admin
     *
     * @date 2019/5/6 16:56
     */
    public function createUserAddress()
    {
        $addressValidate = new AddressValidate();

        $addressValidate->goCheck();

        // 根据token获取用户uid
        $userId = tokenService::getCurrentUidByToken();
        // uid去获取用户信息 没有抛出异常
        $userInfo = User::with(['address'])->where('user_id', '=', $userId)->find();

        if (!$userInfo) {
            throw new UserException([
                'code' => 404,
                'msg' => '用户收获地址不存在',
                'errorCode' => 60001
            ]);
        }

        // 接受添加数据库的数据

        $data = $addressValidate->getDataByRule(input('post.'));

        // 判断用户地址是否存在
        if (!$userInfo->address) {
            //add
            // 没有值的时候是一个null 所有要加() 变成一个对象
            $userInfo->address()->save($data);
        } else {
            //update
            // 有值的时候是一个对象
            $userInfo->address->save($data);
        }

        throw new SuccessMessage();
    }
}