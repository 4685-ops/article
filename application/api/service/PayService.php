<?php


namespace app\api\service;


use app\api\model\Order as orderModel;

use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;
use think\Loader;
use think\Log;

Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');

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
        //检查订单是否合法
        $this->checkOrderIdValidate();

        //4.检查库存
        $status = (new OrderService())->checkOrderStock($this->orderId);

        if (!$status['pass']) {
            return $status;
        }

        $this->makeWxPreOrder($status['orderPrice']);
    }

    //生成微信订单数据
    private function makeWxPreOrder($totalPrice)
    {
        //获取openid
        $openid = TokenService::getUserInfoByVar('openid');

        if (!$openid)
            throw new TokenException();

        $wxOrderData = new \WxPayUnifiedOrder();

        $wxOrderData->SetOut_trade_no($this->orderNo);
        $wxOrderData->SetTrade_type('JSAPI');
        //微信支付 支付的单位是以分结算
        $wxOrderData->SetTotal_fee($totalPrice * 100);
        $wxOrderData->SetBody('零食商贩');
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url('');

        //获取微信支付签名
        return $this->getPaySignature($wxOrderData);
    }

    //得到签名
    private function getPaySignature($wxOrderData)
    {
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);

        if ($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS') {
            Log::record($wxOrder, 'error');
            Log::record('获取预支付订单失败', 'error');
        }

        //保存 prepay_id
        $this->recordPreOrder($wxOrder);

        //生成签名
        $signature = $this->sign($wxOrder);
        return $signature;
    }

    private function sign($wxOrder)
    {
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid(config('wx.app_id'));
        // 要传入的时间戳必须是string
        $jsApiPayData->SetTimeStamp((string)time());
        $rand = md5(time() . mt_rand(0, 1000));
        $jsApiPayData->SetNonceStr($rand);
        $jsApiPayData->SetPackage('prepay_id=' . $wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');
        $sign = $jsApiPayData->MakeSign();
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['paySign'] = $sign;
        unset($rawValues['appId']);

        return $rawValues;
    }

    // 必须是update，每次用户取消支付后再次对同一订单支付，prepay_id是不同的
    // 作用是为了给用户发送消息
    private function recordPreOrder($wxOrder)
    {
        orderModel::where('id', '=', $this->orderID)
            ->update(['prepay_id' => $wxOrder['prepay_id']]);
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