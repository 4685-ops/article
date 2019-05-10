<?php


namespace app\api\controller\v1;


use app\api\service\PayService;
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


}