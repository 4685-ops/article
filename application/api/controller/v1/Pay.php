<?php


namespace app\api\controller\v1;


use app\api\service\PayService;
use app\api\service\WxNotify;
use app\api\validate\IDMustBePositiveInt;


class Pay extends BaseController
{

    protected $beforeActionList = [
        'checkExclusiveScope' => [
            'only' => 'getpreorder'
        ]
    ];

    public function getPreOrder($id = '')
    {
        // 传递一个订单id
        (new IDMustBePositiveInt())->goCheck();
        return (new PayService($id))->pay();
    }

    /**
     * @function   receiveNotify    订单回调
     *
     * @author admin
     *
     * @date 2019/5/15 9:22
     */
    public function receiveNotify()
    {
        $wxNotify = new WxNotify();
        $wxNotify->Handle();
    }
}