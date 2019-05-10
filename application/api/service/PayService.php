<?php


namespace app\api\service;


use app\api\model\Order as orderModel;

use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use think\Exception;

class PayService
{
    protected $orderId = '';
    protected $orderNo = '';

    public function __construct($id)
    {
        if (!$id) {
            throw new Exception('订单号不允许为NULL');
        }

        $this->orderId = $id;
    }

    public function pay()
    {

        $this->checkOrderIdValidate();
        //4.检查库存

        

    }

    /**
     * @function   checkOrderIdValidate
     *
     * 1.检查当前的这个订单id是否正确
     * 2.检查当前的这个订单是不是本人的
     * 3.检查当前的这个订单是否支付过
     *
     * @return bool
     * @throws Exception
     * @throws OrderException
     * @throws \app\lib\exception\TokenException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @author admin
     *
     * @date 2019/5/10 11:52
     */
    private function checkOrderIdValidate()
    {
        $orderInfo = orderModel::where('id', '=', $this->orderId)->find();

        if (!$orderInfo) {
            throw new OrderException();
        }
        $flag = TokenService::isValidOperate($orderInfo->user_id);
        if (!$flag) {
            throw new Exception([
                'msg' => '订单与用户不匹配',
                'errorCode' => 10003
            ]);
        }

        if ($orderInfo->status != OrderStatusEnum::UNPAID) {
            throw new OrderException([
                'msg' => '订单已支付过啦',
                'errorCode' => 80003,
                'code' => 400
            ]);
        }

        $this->orderNo = $orderInfo->order_no;

        return true;

    }
}